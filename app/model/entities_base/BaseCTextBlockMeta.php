<?php
/**
 * Базовая реализация класса [[CTextBlockMeta]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CTextBlockMeta]].
 *
 * @author Dmitriy Lunin
 */
class BaseCTextBlockMeta extends AbstractEntityMeta
{
    /** @see AbstractEntityMeta::DB_TABLE */
    public const DB_TABLE = 'text_blocks';
    public const DB_TABLE_ALIAS = 'tb';

    /** @see AbstractEntityMeta::INSERT_GROUPS */
    protected const INSERT_GROUPS = [Cfg::GRP_ADMINS];
    protected const UPDATE_GROUPS = [Cfg::GRP_ADMINS];
    protected const DELETE_GROUPS = [Cfg::GRP_ADMINS];

    /** @see AbstractEntityMeta::fields */
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
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'content' => [
			'name'          => 'Содержимое блока',
			'description'   => 'HTML-код.',
			'type'          => FieldInfo::FT_HTML,
			'editing_mode'  => FieldInfo::EM_RAW,
			'required'      => false,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		]
	];
}
