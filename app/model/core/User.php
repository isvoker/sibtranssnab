<?php
/**
 * Статичный класс User.
 *
 * Интерфейс для доступа к аккаунту пользователя системы.
 *
 * @author Dmitriy Lunin
 */
class User
{
	/**
	 * Динамический объект аккаунта.
	 *
	 * @var CUser
	 */
	protected static $Entity;

	/**
	 * Ассоциация пользователя с гостевым аккаунтом.
	 */
	protected static function pullGuestAccount(): void
	{
		self::$Entity = UserManager::getByLogin(Cfg::GRP_GUEST);
	}

	/**
	 * Ассоциация пользователя с регулярным аккаунтом.
	 * Если аккаунт не имеет статуса 'active', происходит завершение сессии.
	 *
	 * @param  int  $id  ID аккаунта
	 */
	protected static function pullAccount(int $id): void
	{
		self::$Entity = UserManager::getById($id);

		if (!self::$Entity->isActive()) {
			self::logout();
		}
	}

	/**
	 * Получение "отпечатка" браузера пользователя,
	 * используемого для "запоминания" его устройства.
	 *
	 * @return string
	 */
	protected static function getFingerprint(): string
	{
		return md5(
			Request::getUserAgent()
			. '#' . Request::getServerVar('HTTP_ACCEPT_LANGUAGE')
		);
	}

	/**
	 * Идентификация текущего пользователя по данным сессии.
	 */
	protected static function identification(): void
	{
		if (
			!is_null($id = Session::get('User', 'id'))
			&& Session::get('User', 'token') === self::getFingerprint()
		) {
			try {
				self::pullAccount($id);
			} catch (UserNotFoundEx $E) {
				Session::delete('User');
				self::pullGuestAccount();
			}
		} else {
			Session::delete('User');
			self::pullGuestAccount();
		}

		self::$Entity->pullGroups();
	}

	/**
	 * Получение информации об аккаунте пользователя.
	 *
	 * @param   ?string  $property
	 * @return  mixed
	 */
	protected static function get(?string $property = null)
	{
		if (is_null(self::$Entity)) {
			self::identification();
		}

		if (empty($property)) {
			return clone self::$Entity;
		}

		return self::$Entity->$property;
	}

	/**
	 * Получение аккаунта пользователя.
	 *
	 * @param  ?ObjectOptions  $Options  Параметры создания объекта :
	 * ~~~
	 *   bool  $withExtraData  = false
	 *   bool  $forOutput      = false
	 *   bool  $showSensitive  = false
	 * ~~~
	 * @return CUser
	 */
	public static function getEntity(ObjectOptions $Options = null): CUser
	{
		$User = self::get();

		$Options === null && $Options = new ObjectOptions();

		if ($Options->getWithExtraData()) {
			$User->buildExtraData();
		}
		if ($Options->getForOutput()) {
			$User->getFieldsForOutput();
		}
		if ($Options->getShowSensitive()) {
			$User->password = DBCommand::select([
				'select' => 'password',
				'from'   => CUserMeta::getDBTable(),
				'where'  => 'id = ' . DBCommand::qV($User->id)
			], DBCommand::OUTPUT_FIRST_CELL);
		}

		return $User;
	}

	/**
	 * Получение ID аккаунта пользователя.
	 *
	 * @return int
	 */
	public static function id(): int
	{
		return self::get('id');
	}

	/**
	 * Получение логина аккаунта пользователя.
	 *
	 * @return string
	 */
	public static function login(): string
	{
		return self::get('login');
	}

	/**
	 * Получение имени аккаунта пользователя.
	 *
	 * @return string
	 */
	public static function name(): string
	{
		return self::get('name');
	}

	/**
	 * Получение списка групп текущего пользователя.
	 *
	 * @return array
	 */
	public static function getGroups(): array
	{
		return self::get()->getPrivateExtraData('grp') ?: [];
	}

	/**
	 * Действия, выполняемые при отказе в аутентификации.
	 *
	 * @param  int     $reason  Код причины отказа
	 * @param  string  $login   Логин пользователя
	 */
	protected static function authenticationFailed(int $reason, string $login): void
	{
		$comment = '';

		if (
			Cfg::GUARD_IS_ON
			&& $reason === UserEx::CREDENTIALS_IS_WRONG
		) {
			Guard::logEvent(Guard::CODES['unauthorized'], $login);
			$comment = Guard::getWarning(Guard::CODES['unauthorized']);
		}

		throw new UserEx($reason, $comment);
	}

	/**
	 * Аутентификация пользователя по логину и паролю.
	 * В случае удачи возвращается CMS_LOGIN_PAGE или URI ранее запрошенной страницы
	 * для последующего перенаправления.
	 *
	 * @param   string  $loginEnc     Зашифрованный логин пользователя
	 * @param   string  $passwordEnc  Зашифрованный пароль пользователя
	 * @return  string
	 */
	public static function authentication(string $loginEnc, string $passwordEnc): string
	{
		if (empty($loginEnc) || empty($passwordEnc)) {
			throw new UserEx( UserEx::CREDENTIALS_IS_WRONG );
		}

		$errorCode = 0;

		$User = new CUser(['login' => Security::decryptBySession($loginEnc)]);
		$fields = DBCommand::select([
			'select' => [['id', 'password', 'statuses']],
			'from'   => DBCommand::qC( CUserMeta::getDBTable() ),
			'where'  => 'login = ' . DBCommand::qV($User->login)
		], DBCommand::OUTPUT_FIRST_ROW);

		if (empty($fields)) {
			$errorCode = UserEx::CREDENTIALS_IS_WRONG;
		}

		$User->password = Security::decryptBySession($passwordEnc);

		if (
			!Security::validatePassword($User->password, $fields['password'] ?? '')
			&& $errorCode === 0
		) {
			$errorCode = UserEx::CREDENTIALS_IS_WRONG;
		}

		if ($errorCode === 0) {
			$User->statuses = $fields['statuses'];

			if (
				!$User->isActive()
				|| $User->isBanned()
			) {
				$errorCode = UserEx::STATUS_IS_WRONG;
			}
		}

		if ($errorCode) {
			self::authenticationFailed($errorCode, $User->login);
		}

		$User->id = $fields['id'];
		Session::regenerateID(true);
		Session::delete('User');
		Session::set('User', 'id', $User->id);
		Session::set('User', 'token', self::getFingerprint());

		HistoryManager::addHistory("Аутентификация пользователя #{$User->id} {$User->login}", true);

		return Session::get('access_denied', 'request_uri') ?: Cfg::URL_AFTER_LOGIN;
	}

	/**
	 * Аутентифицирован ли пользователь.
	 *
	 * @return bool
	 */
	public static function isLoggedIn(): bool
	{
		return self::id() === Session::get('User', 'id');
	}

	/**
	 * Состоит ли пользователь в заданных группах.
	 *
	 * @param   mixed  $groups  Имя группы или список имён групп (string|array)
	 * @param   bool   $inAll   Пользователь должен состоять во всех указанных группах
	 *                          или хотя бы в одной из них?
	 * @return  bool
	 */
	public static function isInGroup($groups, $inAll = true): bool
	{
		return self::get()->isInGroup($groups, $inAll);
	}

	/**
	 * Является ли пользователь Администратором.
	 * Результат кэшируется в сессии.
	 *
	 * @return bool
	 */
	public static function isAdmin(): bool
	{
		$result = Session::get('User', 'is_admin');
		if ($result === null) {
			$result = self::isLoggedIn() && self::isInGroup(Cfg::GRP_ADMINS);
			Session::set('User', 'is_admin', $result);
		}

		return $result;
	}

	/**
	 * Завершение сессии пользователя.
	 */
	public static function logout(): void
	{
		Session::unsetAll();
		Session::regenerateID(true);

		HistoryManager::addHistory('Завершение сессии', true);

		Response::redirect(Cfg::URL_AFTER_LOGOUT);
	}

	/**
	 * Обработка запроса на завершение сессии пользователя.
	 */
	public static function doAction(): void
	{
		if (Request::getVar('action', 'string') === 'logout') {
			self::logout();
		}
	}
}
