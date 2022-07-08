<?php
/**
 * Статичный класс Account.
 *
 * Набор методов для работы с аккаунтами пользователей.
 *
 * @author Dmitriy Lunin
 */
class Account
{
	/** Коды действий для генерируемых токенов */
    protected const ACTION_REGISTRATION = 'r';
    protected const ACTION_PASSWORD_RESET = 'p';

	/**
	 * Добавление (регистрация) аккаунта.
	 *
	 * @param  array  $fields   Список полей объекта аккаунта: email[, login, password, name]
	 * @param  array  $groups   Список групп, в которые надо добавить учётную запись
	 * @param  array  $options  Доп. параметры:
	 * ~~~
	 *   bool  $dataIsEncrypted = true   Данные переданы в зашифрованном виде
	 *   bool  $activate        = false  Создать аккаунт уже активированным
	 *   bool  $notify          = true   Отправить email-уведомление?
	 * ~~~
	 * @return  CUser
	 */
	public static function create(
		array $fields,
		array $groups = [],
		array $options = []
	): CUser {
		if (empty($fields['email'])) {
			throw new EntityEditEx(EntityEditEx::FIELD_IS_EMPTY, 'email');
		}

		$dataIsEncrypted = $options['dataIsEncrypted'] ?? true;
		$activate = $options['activate'] ?? false;
		$notify = $options['notify'] ?? true;

		if ($dataIsEncrypted) {
			$plainFields = [];
			foreach ($fields as $field => $value) {
				if (is_string($value) && $value) {
					$plainFields[ $field ] = Security::decryptBySession($value);
				}
			}
			$fields = $plainFields;
		}

		if (empty($fields['login'])) {
			$fields['login'] = $fields['email'];
		}

		$User = new CUser([
			'login'    => $fields['login'],
			'password' => $fields['password'] ?? null,
			'email'    => $fields['email'],
			'name'     => $fields['name'] ?? null
        ], (new ObjectOptions())->setShowSensitive());

		if ($activate) {
			$User->addStatus('active');
		}

		UserManager::add($User, true, $groups);

		if ($notify) {
			$noticeOptions = [
				'recipients' => [
					[$User->email, $User->name]
				],
				'template' => 'accountCreate'
			];

			$noticeData = [
				'user' => [
					'login' => Html::qSC($User->login),
					'name' => Html::qSC($User->name)
				]
			];

			if (!$activate) {
				$token = self::createRegistrationToken($User->id);
				$noticeData['confirmationLink'] = Cfg::URL_ACCOUNT_SIGNUP . '?t=' . $token;
			}

			if (!Notifier::sendMail($noticeOptions, $noticeData)) {
				throw new MailEx();
			}
		}

		return $User;
	}

	/**
	 * Измененение данных аккаунта.
	 * Для изменения email, логина и пароля требуется в [[$fields]]
	 * передать текущее значение пароля аккаунта.
	 *
	 * @param   array  $fields           Список полей объекта аккаунта: email, login, password, new_password, name
	 * @param   bool   $dataIsEncrypted  Данные переданы в зашифрованном виде
	 * @return  CUser
	 */
	public static function update(array $fields, bool $dataIsEncrypted = true): CUser
	{
		if (!User::isLoggedIn()) {
			throw new AccessDeniedEx();
		}

		if ($dataIsEncrypted) {
			$plainFields = [];
			foreach ($fields as $field => $value) {
				if (is_string($value) && $value) {
					$plainFields[ $field ] = Security::decryptBySession($value);
				}
			}
			$fields = $plainFields;
		}

		$oldPassword = $fields['password'] ?? null;

		$User = User::getEntity(
			(new ObjectOptions())->setShowSensitive()
		);
		$NewUser = clone $User;

        if (!empty($oldPassword)) {
			if (!Security::validatePassword($oldPassword, $User->password)) {
				throw new UserEx( UserEx::PASSWORD_IS_INVALID );
			}

			if ($fields['login'] ?? null) {
				$NewUser->login = $fields['login'];
			}

			if ($fields['email'] ?? null) {
				$NewUser->email = $fields['email'];
			}

			if (
				$User->login === $User->email
				&& !isset($fields['login'])
			) {
				$NewUser->login = $NewUser->email;
			}

			if ($fields['new_password'] ?? null) {
				$NewUser->setTrust(
					'password',
					Security::calculatePasswordHash($fields['new_password'])
				);
			}
		}

		if ($fields['name'] ?? null) {
			$NewUser->name = $fields['name'];
		}

		UserManager::update($User, $NewUser, true);

		return $NewUser;
	}

	/**
	 * Удаление аккаунта.
	 */
	public static function delete(): void
	{
		UserManager::delete(
			User::getEntity(),
			true
		);
	}

	/**
	 * Активация аккаунта.
	 *
	 * @param   string  $token  Токен подтверждения
	 * @return  bool    Была ли выполнена операция
	 */
	public static function activate(string $token): bool
	{
		$tokenPayload = self::parseToken($token, self::ACTION_REGISTRATION);
		$User = UserManager::getById($tokenPayload['usr']);

		if ($User->isActive() || $User->isBanned()) {
			return false;
		}

		UserManager::activateUser($User);

		return true;
	}

	/**
	 * Смена пароля аккаунта.
	 *
	 * @param   string  $oldPasswordEnc  Текущий пароль (по версии пользователя) в зашифрованном виде
	 * @param   string  $newPasswordEnc  Новый пароль в зашифрованном виде
	 * @return  bool
	 */
	public static function changePassword(string $oldPasswordEnc, string $newPasswordEnc): bool
	{
		if (!User::isLoggedIn()) {
			throw new AccessDeniedEx();
		}

		$User = User::getEntity(
			(new ObjectOptions())->setShowSensitive()
		);

		$oldPassword = Security::decryptBySession($oldPasswordEnc);
		if (!Security::validatePassword($oldPassword, $User->password)) {
			throw new UserEx( UserEx::PASSWORD_IS_INVALID );
		}

		return UserManager::changePassword($User, $newPasswordEnc);
	}

	/**
	 * Обработка запроса на "сброс" пароля аккаунта.
	 *
	 * @param   string  $loginEnc  Логин в зашифрованном виде
	 * @return  string  Адрес эл. почты, на который была отправлена ссылка для смены пароля
	 */
	public static function passwordResetRequest(string $loginEnc): string
	{
		$User = UserManager::getByLogin( Security::decryptBySession($loginEnc) );

		if (!$User->isActive() || $User->isBanned()) {
			throw new UserEx( UserEx::STATUS_IS_WRONG );
		}

		$token = self::createPasswordResetToken($User->id);

		$noticeOptions = [
			'recipients' => [
				[$User->email, $User->name]
			],
			'template' => 'accountPasswordResetRequest'
		];

		$noticeData = [
			'user' => [
				'login' => Html::qSC($User->login)
			],
			'confirmationLink' => Cfg::URL_ACCOUNT_RESET_PASSWORD . '?t=' . $token
		];

		if (!Notifier::sendMail($noticeOptions, $noticeData)) {
			throw new MailEx();
		}

		return $User->email;
	}

	/**
	 * "Сброс" пароля аккаунта.
	 *
	 * @param  string  $token           Токен подтверждения
	 * @param  string  $newPasswordEnc  Новый пароль в зашифрованном виде
	 */
	public static function passwordReset(string $token, string $newPasswordEnc): void
	{
		$tokenPayload = self::parseToken($token, self::ACTION_PASSWORD_RESET);
		$User = UserManager::getById($tokenPayload['usr']);

		if (!$User->isActive() || $User->isBanned()) {
			throw new UserEx( UserEx::STATUS_IS_WRONG );
		}

		UserManager::changePassword($User, $newPasswordEnc);

		$noticeOptions = [
			'recipients' => [
				[$User->email, $User->name]
			],
			'template' => 'accountPasswordReset'
		];

		$noticeData = [
			'user' => [
				'name' => Html::qSC($User->name)
			]
		];

		if (!Notifier::sendMail($noticeOptions, $noticeData)) {
			throw new MailEx();
		}
	}

	/**
	 * Создание токена для завершения регистрации (активации аккаунта).
	 *
	 * @param   int  $userId  ID аккаунта
	 * @return  string
	 */
	protected static function createRegistrationToken(int $userId): string
	{
		$payload = [
			'act' => self::ACTION_REGISTRATION,
			'usr' => $userId
		];

		return Tokenizer::createJwt($payload, Time::DAY);
	}

	/**
	 * Создание токена для "сброса" пароля аккаунта.
	 *
	 * @param   int  $userId  ID аккаунта
	 * @return  string
	 */
	protected static function createPasswordResetToken(int $userId): string
	{
		$payload = [
			'act' => self::ACTION_PASSWORD_RESET,
			'usr' => $userId
		];

		return Tokenizer::createJwt($payload, Time::HOUR);
	}

	/**
	 * Извлечение из токена полезной нагрузки вида [
	 *   'act' => $action,
	 *   'usr' => $userId
	 * ].
	 *
	 * @param   string  $token   Токен подтверждения
	 * @param   string  $action  Код действия, для которого требуется токен
	 * @return  array
	 */
	protected static function parseToken(string $token, string $action): array
	{
		$payload = Tokenizer::getJwtPayload($token);

		if (
			!is_numeric($payload['usr'] ?? null)
			|| strcmp($payload['act'] ?? '', $action) !== 0
		) {
			throw new TokenizerEx( TokenizerEx::TOKEN_IS_INVALID );
		}

		return $payload;
	}
}
