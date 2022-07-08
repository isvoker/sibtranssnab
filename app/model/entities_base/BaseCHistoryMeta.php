<?php
/**
 * Базовая реализация класса [[CHistoryMeta]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CHistoryMeta]].
 *
 * @author Pavel Nuzhdin <pnzhdin@gmail.com>
 * @author Dmitriy Lunin
 */
class BaseCHistoryMeta extends AbstractEntityMeta
{
    /** @see AbstractEntityMeta::DB_TABLE */
    public const DB_TABLE = 'history';
    public const DB_TABLE_ALIAS = 'h';

    /** @see AbstractEntityMeta::INSERT_GROUPS */
    protected const INSERT_GROUPS = [parent::PERMS_ALL_GROUPS];
    protected const UPDATE_GROUPS = [];
    protected const DELETE_GROUPS = [];

    /** @see AbstractEntityMeta::ACTIONS_TO_BE_TRUSTED */
    protected const ACTIONS_TO_BE_TRUSTED = [
        Action::INSERT => true
    ];

    /** @see AbstractEntityMeta::fields */
	protected $fields = [
		'id' => [
			'name'          => 'ID',
			'type'          => FieldInfo::FT_INTEGER,
			'unsigned'      => true,
			'required'      => false,
			'visible_for'   => [Cfg::GRP_ADMINS]
		],
		'user_id' => [
			'name'          => 'ID учётной записи',
			'description'   => 'ID учётной записи, к которой относится запись в истории.',
			'type'          => FieldInfo::FT_INTEGER,
			'unsigned'      => true,
			'required'      => true,
			'visible_for'   => [Cfg::GRP_ADMINS]
		],
		'user_name' => [
			'name'          => 'Имя учётной записи',
			'description'   => 'Имя учётной записи, к которой относится запись в истории.',
			'type'          => FieldInfo::FT_TEXT,
			'maxlength'     => 255,
			'required'      => true,
			'visible_for'   => [Cfg::GRP_ADMINS]
		],
		'ip' => [
			'name'          => 'IP учётной записи',
			'description'   => 'IP адрес, с которого пользователь совершил действие.',
			'type'          => FieldInfo::FT_TEXT,
			'maxlength'     => 45,
			'required'      => true,
			'visible_for'   => [Cfg::GRP_ADMINS]
		],
		'is_user_history' => [
			'name'          => 'Событие учётной записи',
			'description'   => 'Относится ли событие к действиям с учётной записью.',
			'type'          => FieldInfo::FT_BOOLEAN,
			'required'      => true,
			'visible_for'   => [Cfg::GRP_ADMINS]
		],
		'time' => [
			'name'          => 'Дата и время события',
			'type'          => FieldInfo::FT_DATETIME,
			'required'      => true,
			'visible_for'   => [Cfg::GRP_ADMINS]
		],
		'description' => [
			'name'          => 'Описание события',
			'type'          => FieldInfo::FT_MULTILINETEXT,
			'required'      => true,
			'visible_for'   => [Cfg::GRP_ADMINS]
		]
	];
}
