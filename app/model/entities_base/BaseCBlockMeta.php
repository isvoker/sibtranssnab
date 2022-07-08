<?php
/**
 * Базовая реализация класса [[CBlockMeta]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CBlockMeta]].
 *
 * @author Dmitriy Lunin
 */
class BaseCBlockMeta extends AbstractEntityMeta
{
    /** @see AbstractEntityMeta::DB_TABLE */
    public const DB_TABLE = 'blocks';
    public const DB_TABLE_ALIAS = 'b';

    /** @see AbstractEntityMeta::INSERT_GROUPS */
    public const INSERT_GROUPS = [Cfg::GRP_ADMINS];
    public const UPDATE_GROUPS = [Cfg::GRP_ADMINS];
    public const DELETE_GROUPS = [Cfg::GRP_ADMINS];

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
			'maxlength'     => 32,
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
		'module' => [
			'name'          => 'Модуль',
			'type'          => FieldInfo::FT_TEXT,
			'maxlength'     => 64,
			'editing_mode'  => FieldInfo::EM_TEXT,
			'required'      => false,
			'visible_for'   => [Cfg::GRP_ADMINS],
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'file' => [
			'name'          => 'Файл модуля',
			'type'          => FieldInfo::FT_TEXT,
			'maxlength'     => 64,
			'editing_mode'  => FieldInfo::EM_TEXT,
			'required'      => false,
			'visible_for'   => [Cfg::GRP_ADMINS],
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'props' => [
			'name'          => 'Строка параметров, передаваемых блоку',
			'type'          => FieldInfo::FT_MULTILINETEXT,
			'editing_mode'  => FieldInfo::EM_TEXT,
			'required'      => false,
			'visible_for'   => [Cfg::GRP_ADMINS],
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
		]
	];
}
