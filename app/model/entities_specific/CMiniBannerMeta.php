<?php

/**
 * Метаинформация сущности [[CMiniBanner]].
 *
 */
class CMiniBannerMeta extends AbstractEntityMeta
{
    /** @see [[AbstractEntityMeta]] */
    const DB_TABLE = 'mini_banners';
    const DB_TABLE_ALIAS = 'mb';

    /** @see [[AbstractEntityMeta]] */
    const INSERT_GROUPS = [Cfg::GRP_ADMINS];
    const UPDATE_GROUPS = [Cfg::GRP_ADMINS];
    const DELETE_GROUPS = [Cfg::GRP_ADMINS];

    /** @see [[AbstractEntityMeta]] */
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
        'image' => [
            'name'          => 'Изображение',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'required'      => true,
            'editing_mode'  => FieldInfo::EM_URL,
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'url' => [
            'name'          => 'Ссылка',
            'description'	=> 'Можно указать в качестве ссылки <strong>url</strong> изображения, тогда по клику оно будет открываться на весь экран',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 255,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_URL,
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'is_target_blank' => [
            'name'			=> 'В новой вкладке',
            'description'	=> 'Если указана ссылка, то открывать страницу в новой вкладке',
            'type'			=> FieldInfo::FT_BOOLEAN,
            'editing_mode'	=> FieldInfo::EM_CHECKBOX,
            'required'		=> false,
            'search_mode'	=> FieldInfo::SM_STRICT,
            'editable_for'	=> [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'title' => [
            'name'          => 'Заголовок',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 128,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'description' => [
            'name'          => 'Описание',
            'type'          => FieldInfo::FT_MULTILINETEXT,
            'required'      => false,
            'editing_mode'  => FieldInfo::EM_TEXTAREA,
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ]
    ];
}
