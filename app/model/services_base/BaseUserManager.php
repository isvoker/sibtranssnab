<?php

ClassLoader::loadClass('CUserMeta');
ClassLoader::loadClass('CUser');
ClassLoader::loadClass('UserNotFoundEx');

/**
 * Базовая реализация класса [[UserManager]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[UserManager]].
 *
 * @author Lunin Dmitriy
 */
class BaseUserManager extends EntityManager implements EntityManagerInterface
{
    use UniqueFieldsProcessorTrait;

    /**
     * @see     EntityManager::baseToObjects()
     * @param   array
     * @param   ObjectOptions
     * @return  array
     */
    public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
    {
        return parent::baseToObjects($dbRows, 'CUser', $Options);
    }

    /**
     * @see     EntityManager::add()
     * @param   AbstractEntity  $User
     * @param   bool            $isTrusted
     * @param   array           $groups  Список групп, в которые надо добавить учётную запись
     * @return  CUser
     */
    public static function add(
        AbstractEntity $User,
        bool $isTrusted = false,
        array $groups = []
    ): AbstractEntity {
        if (!self::isUniqueFields($User)) {
            throw new UserEx( UserEx::LOGIN_ALREADY_IN_USE );
        }

        if (is_null($User->password)) {
            $User->password = Randomizer::getPasswordSmart();
        } elseif (
            $User->login
            && $User->email
            && !Security::isStrongPassword($User->password, $User->login, $User->email)
        ) {
            throw new UserEx( UserEx::PASSWORD_IS_WEAK );
        }

        $User->password = Security::calculatePasswordHash($User->password);
        $User = parent::add($User, $isTrusted);
        $User->clearSecretFields();

        if (!empty($groups)) {
            self::addUserToGroups($User, $groups);
        }

        HistoryManager::addHistory(
            "Регистрация учётной записи #{$User->id} {$User->login}",
            true
        );

        return $User;
    }

    /**
     * @see     EntityManager::update()
     * @param   AbstractEntity  $User
     * @param   AbstractEntity  $NewUser
     * @param   bool            $isTrusted
     * @return  CUser
     */
    public static function update(
        AbstractEntity $User,
        AbstractEntity $NewUser,
        bool $isTrusted = false
    ): AbstractEntity {
        if (
            !self::compareUniqueFields($User, $NewUser)
            && !self::isUniqueFields($NewUser)
        ) {
            throw new UserEx( UserEx::LOGIN_ALREADY_IN_USE );
        }

        $NewUser->email = validEmail($NewUser->email);

        HistoryManager::makeFieldsHistory(
            "Обновление данных учётной записи #{$User->id} {$User->login}",
            CUserMeta::getInstance(),
            $User,
            $NewUser,
            true
        );

        parent::update($User, $NewUser, $isTrusted);
        $User->clearSecretFields();

        return $User;
    }

    /**
     * @see    EntityManager::delete()
     * @param  AbstractEntity  $User
     * @param  bool            $isTrusted
     */
    public static function delete(AbstractEntity $User, bool $isTrusted = false): void
    {
        if (!Cfg::USER_DELETION_IS_ON) {
            throw new FeatureNotAvailableEx( FeatureNotAvailableEx::DELETING_USERS_IS_DISABLED );
        }

        parent::delete($User, $isTrusted);

        HistoryManager::addHistory(
            "Удалена учётная запись #{$User->id} {$User->login}",
            true
        );
    }

    /**
     * @see     EntityManager::baseGetById()
     * @param   int            $id
     * @param  ?ObjectOptions  $Options
     * @return  CUser
     */
    public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
    {
        return parent::baseGetById('CUser', 'UserNotFoundEx', $id, $Options);
    }

    /**
     * Получение [Учётной записи] по логину.
     *
     * @param  string         $login    Логин
     * @param ?ObjectOptions  $Options  Параметры создания объекта :
     * ~~~
     *   bool  $withExtraData  = false
     *   bool  $forOutput      = false
     *   bool  $skipValidation = false
     *   bool  $showSensitive  = false
     * ~~~
     * @return  CUser
     */
    public static function getByLogin(string $login, ObjectOptions $Options = null): AbstractEntity
    {
        return self::getByUniqueFields(
            new CUser(['login' => $login]),
            'UserNotFoundEx',
            $Options
        );
    }

    /**
     * @see     EntityManager::baseFetch()
     * @param  ?FetchBy        $FetchBy
     * @param  ?FetchOptions   $FetchOptions
     * @param  ?ObjectOptions  $ObjectOptions
     * @return  array
     */
    public static function fetch(
        FetchBy $FetchBy = null,
        FetchOptions $FetchOptions = null,
        ObjectOptions $ObjectOptions = null
    ): array {
        is_null($FetchBy) && $FetchBy = new FetchBy();

        $group = $FetchBy->getPlain('group');
        if ($group && is_string($group)) {
            is_null($FetchOptions) && $FetchOptions = new FetchOptions();

            $uTbl = CUserMeta::getDBTable();
            $gTbl = CUserMeta::getDBTableGrp();
            $ugTbl = CUserMeta::getDBTableUsrGrp();

            $query['join'] =
                "RIGHT OUTER JOIN `{$ugTbl}` ON `{$uTbl}`.id = `{$ugTbl}`.user_id " .
                "LEFT JOIN `{$gTbl}` ON `{$ugTbl}`.group_id = `{$gTbl}`.id";

            $query['where'][] = [
                'oper' => 'AND',
                'clause' => ":{$gTbl}:.:name: = {groups}",
                'values' => [ $group ]
            ];

            $query['group'] = "`{$uTbl}`.id";

            $orderBy = $FetchOptions->getOrderBy();
            if ($orderBy && is_array($orderBy)) {
                $orderByWithAlias = [];
                foreach ($orderBy as $column => $direction) {
                    $orderByWithAlias[ "{$uTbl}.{$column}" ] = $direction;
                }
                $FetchOptions->setOrderBy($orderByWithAlias);
            }

            $FetchOptions->setQueryAppendix($query);
        }

        return static::baseFetch(
            CUserMeta::getInstance(),
            $FetchBy,
            $FetchOptions,
            $ObjectOptions
        );
    }

    /**
     * Зарегистрирована ли [Учётная запись] с таким логином?
     *
     * @param   string  $login   Логин
     * @param  ?bool    $active  Искать активированную учётную запись (null - не важно)?
     * @return  bool
     */
    public static function loginUsed(string $login, $active = true): bool
    {
        $where = [['clause' => 'login = ' . DBCommand::qV($login)]];
        if (!is_null($active)) {
            $where[] = [
                'oper' => 'AND',
                'clause' => 'MOD(statuses, ' . CUserMeta::STATUS_CODES['active'] . ') '
                    . ($active ? '=' : '!=') . ' 0'
            ];
        }

        return (bool) DBCommand::select([
            'select' => '1',
            'from'   => DBCommand::qC( CUserMeta::getDBTable() ),
            'where'  => $where
        ], DBCommand::OUTPUT_FIRST_CELL);
    }

    /**
     * Переключение статуса [Учётной записи] (если есть - убирается, нет - добавляется).
     *
     * @param  CUser   $User    Учётная запись (достаточно указать только поле 'id')
     * @param  string  $status  Статус
     */
    protected static function changeStatus(CUser $User, string $status): void
    {
        $OldUser = self::getById(
            $User->id,
            (new ObjectOptions())->setShowSensitive()
        );
        $NewUser = clone $OldUser;
        $NewUser->changeStatus($status);
        parent::update($OldUser, $NewUser, true);

        HistoryManager::addHistory(
            "Переключён статус '{$status}' учётной записи #{$OldUser->id} {$OldUser->login}",
            true
        );
    }

    /**
     * Переключение статуса 'active' [Учётной записи].
     *
     * Проверку правомерности этой операции должен обеспечивать
     * вызывающий код!
     *
     * @param  CUser  $User  Учётная запись
     */
    public static function activateUser(CUser $User): void
    {
        self::changeStatus($User, 'active');
    }

    /**
     * Переключение статуса 'banned' [Учётной записи].
     *
     * Проверку правомерности этой операции должен обеспечивать
     * вызывающий код!
     *
     * @param  CUser  $User  Учётная запись
     */
    public static function blockUser(CUser $User): void
    {
        self::changeStatus($User, 'banned');
    }

    /**
     * Смена пароля [Учётной записи].
     *
     * Проверку правомерности этой операции должен обеспечивать
     * вызывающий код!
     *
     * @param   CUser   $User            Учётная запись
     * @param   string  $newPasswordEnc  Зашифрованный новый пароль
     * @param   array   $options         Дополнительные опции:
     * ~~~
     *   bool   $skipIfEmpty   = true    Не изменять пароль на пустое значение
     *   bool   $checkStrength = true    Надо ли проверить надёжность пароля
     * ~~~
     * @return  bool    TRUE при успешном выполнении операции
     */
    public static function changePassword(
        CUser $User,
        string $newPasswordEnc,
        array $options = []
    ): bool {
        $newPassword = Security::decryptBySession($newPasswordEnc);

        if (
            ($options['skipIfEmpty'] ?? true)
            && trim($newPassword) === ''
        ) {
            return false;
        }

        if (
            ($options['checkStrength'] ?? true)
            && !Security::isStrongPassword($newPassword, $User->login, $User->email)
        ) {
            throw new UserEx( UserEx::PASSWORD_IS_WEAK );
        }

        $NewUser = clone $User;
        $NewUser->setTrust('password', Security::calculatePasswordHash($newPassword));
        parent::update($User, $NewUser, true);
        $User->clearSecretFields();

        HistoryManager::addHistory(
            "Изменён пароль учётной записи #{$User->id} {$User->login}",
            true
        );

        return true;
    }

    /**
     * Получение списка всех групп [Учётных записей].
     *
     * @return array
     */
    public static function getAllGroups(): array
    {
        return DBCommand::select(['from' => CUserMeta::getDBTableGrp()]);
    }

    /**
     * Помещение [Учётной записи] строго в заданный список групп.
     *
     * @param  CUser  $User    Учётная запись
     * @param  array  $groups  Список групп
     */
    public static function setUserGroups(CUser $User, array $groups): void
    {
        if (!User::isAdmin()) {
            throw new AccessDeniedEx();
        }
        if (!$User->id) {
            throw new InvalidArgumentException('User ID is not defined');
        }

        self::removeUserFromGroups($User);
        if (!empty($groups)) {
            self::addUserToGroups($User, $groups);
        }
    }

    /**
     * Добавление [Учётной записи] в заданные группы.
     *
     * @param  CUser  $User    Учётная запись
     * @param  array  $groups  Список групп
     */
    protected static function addUserToGroups(CUser $User, array $groups): void
    {
        DBCommand::query(
            'INSERT INTO ' . CUserMeta::getDBTableUsrGrp() . ' (user_id, group_id) ' .
            'SELECT ' . DBCommand::qV($User->id) . ', g.id ' .
            'FROM ' . CUserMeta::getDBTableGrp() . ' g ' .
            'WHERE name IN (' . arrayToStr($groups, ', ', 'DBCommand::qV') . ')'
        );
    }

    /**
     * Удаление [Учётной записи] из заданных групп.
     *
     * @param  CUser  $User    Учётная запись
     * @param  array  $groups  Список групп. Пустой список подразумевает ВСЕ группы.
     */
    protected static function removeUserFromGroups(CUser $User, array $groups = []): void
    {
        $userId = DBCommand::qV($User->id);
        if (empty($groups)) {
            DBCommand::delete(CUserMeta::getDBTableUsrGrp(), 'user_id = ' . $userId);
            return;
        }

        $grpIDsQuery = DBQueryBuilder::select([
            'select' => 'id',
            'from' => CUserMeta::getDBTableGrp(),
            'where' => 'name IN (' . arrayToStr($groups, ', ', 'DBCommand::qV') . ')'
        ]);
        DBCommand::delete(CUserMeta::getDBTableUsrGrp(), "user_id = {$userId} AND group_id IN ({$grpIDsQuery})");
    }

    /**
     * @deprecated
     * @see     User::getEntity()
     * @param  ?ObjectOptions  $Options
     * @return  CUser
     */
    public static function getCurrentUser(ObjectOptions $Options = null): CUser
    {
        trigger_error(
            'The UserManager::getCurrentUser() method is no longer supported',
            E_USER_DEPRECATED
        );
        return User::getEntity($Options);
    }

    /**
     * @deprecated
     * @see     User::id()
     * @return  int
     */
    public static function getCurrentUserId(): int
    {
        trigger_error(
            'The UserManager::getCurrentUserId() method is no longer supported',
            E_USER_DEPRECATED
        );
        return User::id();
    }

    /**
     * @deprecated
     * @see     User::getGroups()
     * @return  array
     */
    public static function getCurrentUserGroups(): array
    {
        trigger_error(
            'The UserManager::getCurrentUserGroups() method is no longer supported',
            E_USER_DEPRECATED
        );
        return User::getGroups();
    }

    /**
     * @deprecated
     * @see     User::isLoggedIn()
     * @return  bool
     */
    public static function curUserIsAuth(): bool
    {
        trigger_error(
            'The UserManager::curUserIsAuth() method is no longer supported',
            E_USER_DEPRECATED
        );
        return User::isLoggedIn();
    }

    /**
     * @deprecated
     * @see     User::isInGroup()
     * @param   mixed  $groups
     * @param   bool   $inAll
     * @return  bool
     */
    public static function curUserInGrp($groups, $inAll = true): bool
    {
        trigger_error(
            'The UserManager::curUserInGrp() method is no longer supported',
            E_USER_DEPRECATED
        );
        return User::isInGroup($groups, $inAll);
    }

    /**
     * @deprecated
     * @see     User::isAdmin()
     * @return  bool
     */
    public static function curUserIsAdmin(): bool
    {
        trigger_error(
            'The UserManager::curUserIsAdmin() method is no longer supported',
            E_USER_DEPRECATED
        );
        return User::isAdmin();
    }
}
