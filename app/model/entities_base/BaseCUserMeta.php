<?php
/**
 * Базовая реализация класса [[CUserMeta]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CUserMeta]].
 *
 * @author Dmitriy Lunin
 */
class BaseCUserMeta extends AbstractEntityMeta
{
    use StatusesMetaTrait;
    use UniqueFieldsMetaTrait;

    /** @see AbstractEntityMeta::DB_TABLE */
    public const DB_TABLE = 'users';
    public const DB_TABLE_ALIAS = 'u';

    /** Таблица БД, содержащая группы учётных записей */
    protected const DB_TABLE_GRP = 'groups';

    /** Таблица БД, содержащая связи групп и учётных записей */
    protected const DB_TABLE_USR_GRP = 'user_groups';

	/** @see AbstractEntityMeta::SECRET_FIELDS */
	public const SECRET_FIELDS = ['password'];

    /** @see UniqueFieldsMetaTrait */
    public const UNIQUE_KEY = ['login'];

    /** @see StatusesMetaTrait */
    public const STATUS_CODES = [
        'active' => 2,
        'banned' => 3
    ];

    /** @see StatusesMetaTrait */
    public const STATUS_DESCRIPTIONS = [
        self::STATUS_CODES['active'] => 'Учётная запись активирована',
        self::STATUS_CODES['banned'] => 'Учётная запись заблокирована'
    ];

    public const HISTORY_IDENT = 'user';

    /** @see AbstractEntityMeta::INSERT_GROUPS */
    protected const INSERT_GROUPS = [parent::PERMS_ALL_GROUPS];
    protected const UPDATE_GROUPS = [parent::PERMS_ALL_GROUPS];
    protected const DELETE_GROUPS = [Cfg::GRP_ADMINS];

    /** @see AbstractEntityMeta::ACTIONS_TO_BE_TRUSTED */
    protected const ACTIONS_TO_BE_TRUSTED = [
        Action::INSERT => true,
        Action::UPDATE => true,
        Action::DELETE => true
    ];

    /** @see AbstractEntityMeta::fields */
    protected $fields = [
        'id' => [
            'name'          => 'ID',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'search_mode'   => FieldInfo::SM_STRICT,
            'visible_for'   => [],
            'editable_for'  => []
        ],
        'login' => [
            'name'          => 'Логин',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'required'      => true,
            'search_mode'   => FieldInfo::SM_SIMPLE,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'password' => [
            'name'          => 'Хэш пароля',
            'type'          => FieldInfo::FT_PASS,
            'maxlength'     => 255,
            'required'      => true,
            'visible_for'   => [],
            'editable_for'  => [],
            'history_ignore'=> true
        ],
        'email' => [
            'name'          => 'Адрес электронной почты',
            'type'          => FieldInfo::FT_EMAIL,
            'maxlength'     => 255,
            'required'      => true,
            'search_mode'   => FieldInfo::SM_STRICT,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'name' => [
            'name'          => 'Имя',
            'description'   => 'Отображаемое имя учётной записи.',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'required'      => true,
            'search_mode'   => FieldInfo::SM_SIMPLE,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'description' => [
            'name'          => 'Описание',
            'type'          => FieldInfo::FT_MULTILINETEXT,
            'required'      => false,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'statuses' => [
            'name'          => 'Текущие статусы',
            'description'   => 'Произведение кодов текущих статусов учётной записи.',
            'type'          => FieldInfo::FT_STATUSES,
            'unsigned'      => true,
            'required'      => false,
            'default'       => 1,
            'search_mode'   => FieldInfo::SM_STATUSES,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => []
        ],
        'created_at' => [
            'name'          => 'Время создания',
            'description'   => 'Дата и время регистрации учётной записи в системе.',
            'type'          => FieldInfo::FT_DATETIME,
            'required'      => false,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [],
            'history_ignore'=> true
        ],
        'updated_at' => [
            'name'          => 'Время изменения',
            'description'   => 'Дата и время последнего изменения учётной записи.',
            'type'          => FieldInfo::FT_DATETIME,
            'required'      => false,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [],
            'history_ignore'=> true
        ]
    ];

    /**
     * Получение названия таблицы с группами.
     *
     * @return string
     */
    public static function getDBTableGrp(): string
    {
        return Cfg::DB_TBL_PREFIX . self::DB_TABLE_GRP;
    }

    /**
     * Получение названия таблицы со связями групп и учётных записей.
     *
     * @return string
     */
    public static function getDBTableUsrGrp(): string
    {
        return Cfg::DB_TBL_PREFIX . self::DB_TABLE_USR_GRP;
    }
}
