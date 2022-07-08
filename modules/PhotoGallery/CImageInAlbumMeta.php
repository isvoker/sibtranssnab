<?php
/**
 * Метаинформация сущности [Изображение в Альбоме].
 *
 * @author Dmitriy Lunin
 */
class CImageInAlbumMeta extends AbstractEntityMeta
{
	/** @see AbstractEntityMeta::DB_TABLE */
	public const DB_TABLE = 'gallery_images';
	public const DB_TABLE_ALIAS = 'iia';

	/** @see AbstractEntityMeta::INSERT_GROUPS */
	protected const INSERT_GROUPS = [Cfg::GRP_ADMINS];
	protected const UPDATE_GROUPS = [Cfg::GRP_ADMINS]; // + доступ к ajax-контроллеру "admin_gallery"
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
		'album_id' => [
			'name'          => 'Альбом',
			'type'          => FieldInfo::FT_INTEGER,
			'unsigned'      => true,
			'editing_mode'  => FieldInfo::EM_SELECT_FROM_DB,
			'editing_param' => [
				'select' => [['value' => 'id', 'text' => 'name']],
				'from' => CAlbumMeta::DB_TABLE,
				'where' => 'page_id',
				'order' => 'id'
			],
			'required'      => true,
			'search_mode'   => FieldInfo::SM_STRICT,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'img_path' => [
			'name'          => 'Ссылка на изображение',
			'type'          => FieldInfo::FT_TEXT,
			'maxlength'     => 255,
			'editing_mode'  => FieldInfo::EM_URL,
			'required'      => true,
			'visible_for'   => [],
			'editable_for'  => []
		],
		'name' => [
			'name'          => 'Имя',
			'description'   => 'Отображается на полупрозрачной обложке поверх изображения',
			'type'          => FieldInfo::FT_TEXT,
			'editing_mode'  => FieldInfo::EM_TEXT,
			'required'      => false,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'caption' => [
			'name'          => 'Подпись',
			'description'   => 'Отображается под изображением в полноэкранном режиме',
			'type'          => FieldInfo::FT_TEXT,
			'editing_mode'  => FieldInfo::EM_TEXT,
			'required'      => false,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'posit' => [
			'name'          => 'Позиция в списке изображений',
			'description'   => 'Чем меньше, тем выше будет изображение в списке.',
			'type'          => FieldInfo::FT_INTEGER,
			'unsigned'      => true,
			'maxlength'     => 3,
			'required'      => false,
			'default'       => 1,
			'visible_for'   => [],
			'editable_for'  => []
		],
		'created_at' => [
			'name'          => 'Время создания',
			'description'   => 'Дата и время добавления изображения.',
			'type'          => FieldInfo::FT_DATETIME,
			'required'      => false,
			'editable_for'  => []
		],
		'updated_at' => [
			'name'          => 'Время изменения',
			'description'   => 'Дата и время последнего изменения изображения.',
			'type'			=> FieldInfo::FT_DATETIME,
			'required'		=> false,
			'editable_for'	=> []
		]
	];
}
