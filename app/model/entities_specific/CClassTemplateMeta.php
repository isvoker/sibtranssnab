<?php
/**
 * Метаинформация сущности [[CClassTemplate]].
 *
 * @author Dmitriy Lunin
 */
class CClassTemplateMeta extends AbstractEntityMeta
{
    use StatusesMetaTrait;

    /** @see AbstractEntityMeta::DB_TABLE */
    public const DB_TABLE = 'table_name';
    public const DB_TABLE_ALIAS = 'tn';

    /** @see AbstractEntityMeta::INSERT_GROUPS */
    protected const INSERT_GROUPS = [Cfg::GRP_ADMINS];
    protected const UPDATE_GROUPS = [Cfg::GRP_ADMINS];
    protected const DELETE_GROUPS = [Cfg::GRP_ADMINS];

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
        'boolean_checkbox_strict' => [
            'name'          => 'FT_BOOLEAN / EM_CHECKBOX / SM_STRICT',
            'description'   => 'Описание поля',
            'type'          => FieldInfo::FT_BOOLEAN,
            'required'      => false,
            'default'       => 0,
            'editing_mode'  => FieldInfo::EM_CHECKBOX,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'date_text_interval' => [
            'name'          => 'FT_DATE / EM_TEXT / SM_INTERVAL',
            'type'          => FieldInfo::FT_DATE,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_INTERVAL
        ],
        'time_text_strict' => [
            'name'          => 'FT_TIME / EM_TEXT / SM_STRICT',
            'type'          => FieldInfo::FT_TIME,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'datetime_text_strict' => [
            'name'          => 'FT_DATETIME / EM_TEXT / SM_STRICT',
            'description'   => 'Оставьте пустым для текущего времени',
            'type'          => FieldInfo::FT_DATETIME,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'integer_text_strict' => [
            'name'          => 'FT_INTEGER / EM_TEXT / SM_STRICT',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'float_text_strict' => [
            'name'          => 'FT_FLOAT / EM_TEXT / SM_STRICT',
            'type'          => FieldInfo::FT_FLOAT,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'text_text_simple' => [
            'name'          => 'FT_TEXT / EM_TEXT / SM_SIMPLE',
            'description'   => 'Описание поля',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 64,
            'required'      => true,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_SIMPLE
        ],
        'text_text_strict' => [
            'name'          => 'FT_TEXT / EM_TEXT / SM_STRICT',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 32,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'text_url' => [
            'name'          => 'FT_TEXT / EM_URL',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_URL
        ],
        'pass_password_strict' => [
            'name'          => 'FT_PASS / EM_TEXT / SM_STRICT',
            'type'          => FieldInfo::FT_PASS,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'multilinetext_textarea_simple' => [
            'name'          => 'FT_MULTILINETEXT / EM_TEXTAREA / SM_SIMPLE',
            'type'          => FieldInfo::FT_MULTILINETEXT,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXTAREA,
            'search_mode'   => FieldInfo::SM_SIMPLE
        ],
        'html_htmleditor' => [
            'name'          => 'FT_HTML / EM_HTMLEDITOR',
            'type'          => FieldInfo::FT_HTML,
            'editing_mode'  => FieldInfo::EM_HTMLEDITOR,
            'required'      => false
        ],
        'integer_selectFromDb_strict' => [
            'name'          => 'FT_INTEGER / EM_SELECT_FROM_DB / SM_LIST',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => true,
            'editing_mode'  => FieldInfo::EM_SELECT_FROM_DB,
            'editing_param' => [
                'select' => [['value' => 'id', 'text' => 'name']],
                'from' => 'pages',
                'where' => 'parent = \'1\'',
                'order' => 'text'
            ],
            'search_mode'   => FieldInfo::SM_LIST
        ],
        'integer_autocomplete_strict' => [
            'name'          => 'FT_INTEGER / EM_AUTOCOMPLETE / SM_STRICT',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_AUTOCOMPLETE,
            'editing_param' => [ 'controller' => 'test_autocomplete' ],
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'integer_dicAutocomplete_strict' => [
            'name'          => 'FT_INTEGER / EM_DIC_AUTOCOMPLETE / SM_STRICT',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'dic'           => 'FAUCETS',
            'editing_mode'  => FieldInfo::EM_DIC_AUTOCOMPLETE,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'integer_dicSelect_strict' => [
            'name'          => 'FT_INTEGER / EM_DIC_SELECT / SM_STRICT',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'dic'           => 'PRODUCT_CATEGORIES',
            'editing_mode'  => FieldInfo::EM_DIC_SELECT,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'integer_dicMultiSelect_strict' => [
            'name'          => 'FT_INTEGER / EM_DIC_SELECT (multi) / SM_LIST',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'dic'           => 'PRODUCT_CATEGORIES',
            'dic_multi_max' => 3,
            'editing_mode'  => FieldInfo::EM_DIC_SELECT,
            'search_mode'   => FieldInfo::SM_LIST
        ],
        'integer_dicTree_strict' => [
            'name'          => 'FT_INTEGER / EM_DIC_TREE / SM_STRICT',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'dic'           => 'KITCHEN_SINKS',
            'editing_mode'  => FieldInfo::EM_DIC_TREE,
            'search_mode'   => FieldInfo::SM_STRICT
        ],
        'integer_dicMultiTree_strict' => [
            'name'          => 'FT_INTEGER / EM_DIC_TREE (multi) / SM_LIST',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'dic'           => 'KITCHEN_SINKS',
            'dic_multi_max' => 3,
            'editing_mode'  => FieldInfo::EM_DIC_TREE,
            'search_mode'   => FieldInfo::SM_LIST
        ],
        'statuses' => [
            'name'          => 'FT_STATUSES / SM_STATUSES',
            'type'          => FieldInfo::FT_STATUSES,
            'unsigned'      => true,
            'required'      => false,
            'default'       => 2,
            'search_mode'   => FieldInfo::SM_STATUSES,
            'editable_for'  => []
        ]
    ];
}
