<?php

ClassLoader::preloadClass('entities_base/BaseCBlockMeta');

/**
 * Метаинформация сущности [Блок страницы].
 *
 * @author Dmitriy Lunin
 */
class CBlockMeta extends BaseCBlockMeta {
    const DB_TABLE = 'modules';
    const DB_TABLE_ALIAS = 'm';

    const DB_TABLE_SETTINGS = 'modules_settings';
    const DB_TABLE_MODULES_TO_TEMPLATES = 'modules_to_templates';
    const DB_TABLE_REGISTERED_TRIGGERS = 'registered_triggers';

    /** @see [[AbstractEntityMeta]] */
    protected $fields = [
        'id' => [
            'name'          => 'ID',
            'type'          => FieldInfo::FT_INTEGER,
            'unsigned'      => true,
            'required'      => false,
            'visible_for'   => [],
            'editable_for'  => []
        ],
        'ident' => [
            'name'          => 'Идентификатор',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 64,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => true,
            'search_mode'   => FieldInfo::SM_STRICT,
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS],
            'visible_for'   => [Cfg::GRP_ADMINS]
        ],
        'name' => [
            'name'          => 'Название',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 64,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => true,
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'is_page' => [
            'name'          => 'Блок-страница',
            'description'   => 'Может ли блок быть отдельной страницей.',
            'type'          => FieldInfo::FT_BOOLEAN,
            'editing_mode'  => FieldInfo::EM_CHECKBOX,
            'required'      => false,
            'default'       => 0,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'is_widget' => [
            'name'          => 'Блок-виджет',
            'description'   => 'Может ли блок подключаться в виде виджета.',
            'type'          => FieldInfo::FT_BOOLEAN,
            'editing_mode'  => FieldInfo::EM_CHECKBOX,
            'required'      => false,
            'default'       => 0,
            'visible_for'   => [Cfg::GRP_ADMINS],
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ]
    ];

    /**
     * Получение названия таблицы настроек блока в БД.
     *
     * @return  string
     */
    public static function getDBTableSettings(): string
    {
        return Cfg::DB_TBL_PREFIX . static::DB_TABLE_SETTINGS;
    }

    /**
     * Получение названия таблицы связи блоков с шаблонами в БД
     *
     * @return  string
     */
    public static function getDBTableModulesToTemplates(): string
    {
        return Cfg::DB_TBL_PREFIX . static::DB_TABLE_MODULES_TO_TEMPLATES;
    }

    /**
     * Получение названия таблицы триггеров в БД
     *
     * @return  string
     */
    public static function getDBTableTriggers(): string
    {
        return Cfg::DB_TBL_PREFIX . static::DB_TABLE_REGISTERED_TRIGGERS;
    }
}
