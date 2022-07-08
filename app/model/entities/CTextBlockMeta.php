<?php

ClassLoader::preloadClass('entities_base/BaseCTextBlockMeta');

/**
 * Метаинформация сущности [Текстовый блок].
 *
 * @author Dmitriy Lunin
 */
class CTextBlockMeta extends BaseCTextBlockMeta
{
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
            'maxlength'     => 128,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => true,
            'visible_for'   => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'description' => [
            'name'          => 'Описание текстового блока',
            'description'   => 'Информация о назначении и расположении текстового блока',
            'type'          => FieldInfo::FT_TEXT,
            'maxlength'     => 128,
            'editing_mode'  => FieldInfo::EM_TEXT,
            'required'      => true,
            'visible_for'   => [FieldInfo::PERMS_UPDATE_GROUPS]
        ],
        'content' => [
            'name'          => 'Содержимое блока',
            'description'   => 'HTML-код.',
            'type'          => FieldInfo::FT_HTML,
            'editing_mode'  => FieldInfo::EM_HTMLEDITOR,
            'required'      => false,
            'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
        ]
    ];
}
