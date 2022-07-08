<?php
/**
 * Базовая реализация класса [[CPageMeta]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CPageMeta]].
 *
 * @author Dmitriy Lunin
 */
class BaseCPageMeta extends AbstractEntityMeta
{
    use UniqueFieldsMetaTrait;

    /** @see AbstractEntityMeta::DB_TABLE */
    public const DB_TABLE = 'pages';
    public const DB_TABLE_ALIAS = 'p';

    /** Таблица в БД, описывающая права доступа к страницам разных групп пользователей */
    protected const DB_TABLE_PERMISSIONS = 'page_permissions';

    /** @see UniqueFieldsMetaTrait */
    public const UNIQUE_KEY = ['parent', 'ident'];

    /** @see AbstractEntityMeta::INSERT_GROUPS */
    protected const INSERT_GROUPS = [Cfg::GRP_ADMINS];
    protected const UPDATE_GROUPS = [Cfg::GRP_ADMINS]; // + доступ к ajax-контроллеру "admin_pages"
    protected const DELETE_GROUPS = [Cfg::GRP_ADMINS];

    /** Коды прав доступа */
    public const PERMS = [
        'reed'  => 2,
        'write' => 3
    ];

    /** Расшифровка кодов прав доступа */
    public const PERMS_DESCRIPTION = [
        self::PERMS['reed']  => 'Чтение',
        self::PERMS['write'] => 'Редактирование'
    ];

    /** @see AbstractEntityMeta::fields */
    protected $fields = [
        'id' => [
            'name'          => 'ID',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'		=> true,
            'required'      => false,
            'search_mode'   => FieldInfo::SM_STRICT,
            'visible_for'   => [],
            'editable_for'  => []
        ],
        'parent' => [
            'name'			=> 'Родительский раздел',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'		=> true,
            'editing_mode'  => FieldInfo::EM_SELECT_FROM_DB,
            'editing_param'	=> [
                'select' => [['value' => 'id', 'text' => 'CONCAT(name, \' - \', full_path)']],
                'from'   => self::DB_TABLE,
                'order'  => ['parent' => DBQueryBuilder::ASC, 'posit' => DBQueryBuilder::ASC]
            ],
            'required'      => true,
            'search_mode'   => FieldInfo::SM_STRICT,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'posit' => [
            'name'			=> 'Позиция в списке страниц',
            'description'	=> 'Чем меньше, тем выше будет страница в списке.',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'		=> true,
            'required'      => false,
            'default'		=> 1,
            'visible_for'   => [],
            'editable_for'	=> []
        ],
        'name' => [
            'name'			=> 'Название',
            'description'	=> 'Название страницы в меню и "хлебных крошках".',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'		=> 128,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => true,
            'search_mode'   => FieldInfo::SM_SIMPLE,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'ident' => [
            'name'          => 'Идентификатор',
            'description'	=> 'Уникальный в пределах своего раздела идентификатор. Станет последней частью в URL страницы (например, "https://адрес.сайта/<strong>my_page</strong>/"). Разрешено использовать только латинские буквы, цифры, символы "-", "_", "." и "~". Регистр не учитывается.',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 128,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => true,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS],
            'regexp'		=> '^[A-Za-z0-9\-_\.~]+$',
            'encode_from'	=> 'name'
        ],
        'full_path' => [
            'name'          => 'Путь к странице относительно корня сайта',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'required'      => true,
            'search_mode'   => FieldInfo::SM_SIMPLE,
            'visible_for'   => [],
            'editable_for'	=> []
        ],
        'h1' => [
            'name'          => 'Заголовок первого уровня (h1)',
            'description'   => 'Значение тега "h1". Если не задано, то заголовок не отображается.',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'content' => [
            'name'          => 'Содержимое',
            'description'	=> 'Редактируемый контент страницы (HTML-код).',
            'type'          => FieldInfo::FT_HTML,
            'editing_mode'  => FieldInfo::EM_HTMLEDITOR,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'content_mobile' => [
            'name'          => 'Содержимое для мобильной версии',
            'description'	=> 'Редактируемый контент страницы (HTML-код).',
            'type'          => FieldInfo::FT_HTML,
            'editing_mode'  => FieldInfo::EM_HTMLEDITOR,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'direct_link' => [
            'name'			=> 'Ссылка для перехода',
            'description'   => 'При открытии страницы будет осуществлено перенаправление по указанному адресу.',
            'type'			=> FieldInfo::FT_TEXT,
            'maxlength'		=> 255,
            'editing_mode'	=> FieldInfo::EM_URL,
            'required'		=> false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'in_menu' => [
            'name'			=> 'Присутствует в меню',
            'description'	=> 'Включение страницы в автоматически создаваемое меню.',
            'type'			=> FieldInfo::FT_BOOLEAN,
            'editing_mode'	=> FieldInfo::EM_CHECKBOX,
            'required'		=> false,
            'search_mode'	=> FieldInfo::SM_STRICT,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'not_clickable' => [
            'name'          => 'Пункт меню не кликабельный',
            'description'   => 'Отключение возможности перехода на страницу по ссылке в меню.',
            'type'          => FieldInfo::FT_BOOLEAN,
            'editing_mode'  => FieldInfo::EM_CHECKBOX,
            'required'      => false,
            'default'		=> 0,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'in_map' => [
            'name'			=> 'Отображается на карте сайта',
            'type'			=> FieldInfo::FT_BOOLEAN,
            'editing_mode'	=> FieldInfo::EM_CHECKBOX,
            'required'		=> false,
            'default'		=> 1,
            'search_mode'	=> FieldInfo::SM_STRICT,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'noindex' => [
            'name'			=> 'Не индексировать содержимое страницы',
            'description'	=> 'Рекомендовать поисковым роботам не индексировать содержимое страницы.',
            'type'			=> FieldInfo::FT_BOOLEAN,
            'editing_mode'	=> FieldInfo::EM_CHECKBOX,
            'required'		=> false,
            'search_mode'	=> FieldInfo::SM_STRICT,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'is_public' => [
            'name'			=> 'Публичная страница',
            'description'	=> 'Настройки прав доступа для групп пользователей не учитываются.',
            'type'			=> FieldInfo::FT_BOOLEAN,
            'editing_mode'	=> FieldInfo::EM_CHECKBOX,
            'required'		=> false,
            'default'		=> 1,
            'search_mode'	=> FieldInfo::SM_STRICT,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'is_hidden' => [
            'name'			=> 'Страница скрыта',
            'description'	=> 'Просмотр страницы доступен только Администратору.',
            'type'			=> FieldInfo::FT_BOOLEAN,
            'editing_mode'	=> FieldInfo::EM_CHECKBOX,
            'required'		=> false,
            'default'		=> 0,
            'search_mode'	=> FieldInfo::SM_STRICT,
            'editable_for'	=> [Cfg::GRP_ADMINS]
        ],
        'title' => [
            'name'			=> 'Заголовок',
            'description'	=> 'Значение тега "title". Если пусто, будет использовано значение поля "Название".',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'		=> 255,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'description' => [
            'name'			=> 'Описание',
            'description'	=> 'Значение атрибута метатега "description".',
            'type'          => FieldInfo::FT_MULTILINETEXT,
            'editing_mode'  => FieldInfo::EM_TEXTAREA,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'keywords' => [
            'name'			=> 'Ключевые слова',
            'description'	=> 'Значение атрибута метатега "keywords".',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'main_template' => [
            'name'          => 'Главный шаблон',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 32,
            'editing_mode'  => FieldInfo::EM_SELECT_FROM_DB,
            'editing_param' => [
                'select' => 'ident AS value, name AS text',
                'from'   => Application::DB_TABLE_MAIN_TEMPLATES,
                'order'  => ['posit' => DBQueryBuilder::ASC]
            ],
            'required'      => true,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'module' => [
            'name'          => 'Модуль',
            'description'   => 'Модуль, формирующий наполнение страницы.',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'		=> 32,
            'editing_mode'  => FieldInfo::EM_SELECT_FROM_DB,
            'editing_param' => [
                'select' => [['value' => 'ident', 'text' => 'name']],
                'from'   => CBlockMeta::DB_TABLE,
                'where'  => 'is_page = 1',
                'order'  => 'id'
            ],
            'required'		=> true,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'controller_dir' => [
            'name'          => 'Директория контроллера',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 64,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'controller_file' => [
            'name'          => 'Файл Контроллера',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 64,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => false,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'props' => [
            'name'			=> 'Служебные параметры',
            'description'	=> 'Строка параметров, передаваемых блоку и модулю страницы.',
            'type'			=> FieldInfo::FT_MULTILINETEXT,
            'required'		=> false,
            'editable_for'	=> []
        ],
        'is_system' => [
            'name'          => 'Системная страница',
            'type'          => FieldInfo::FT_BOOLEAN,
            'editing_mode'  => FieldInfo::EM_CHECKBOX,
            'required'      => false,
            'default'		=> 0,
            'editable_for'	=> []
        ],
        'is_fixed' => [
            'name'			=> 'Страница зафиксирована',
            'description'   => 'Нельзя изменить "Родительский раздел", "Идентификатор", "Главный шаблон" и "Ссылку для перехода". Запрещены удаление и перемещение страницы.',
            'type'			=> FieldInfo::FT_BOOLEAN,
            'editing_mode'	=> FieldInfo::EM_CHECKBOX,
            'required'		=> false,
            'editable_for'	=> []
        ],
        'created_at' => [
            'name'          => 'Время создания',
            'description'   => 'Дата и время добавления страницы.',
            'type'			=> FieldInfo::FT_DATETIME,
            'required'		=> false,
            'editable_for'	=> []
        ],
        'updated_at' => [
            'name'          => 'Время изменения',
            'description'   => 'Дата и время последнего изменения данных.',
            'type'			=> FieldInfo::FT_DATETIME,
            'required'		=> false,
            'editable_for'	=> []
        ]
    ];

    /**
     * Получение названия таблицы в БД, описывающей права доступа.
     *
     * @return string
     */
    public static function getDBTablePermissions(): string
    {
        return Cfg::DB_TBL_PREFIX . self::DB_TABLE_PERMISSIONS;
    }
}
