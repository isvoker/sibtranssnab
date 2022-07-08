<?php
/**
 * Метаинформация сущности [Альбом].
 *
 * @author Dmitriy Lunin
 */
class CAlbumMeta extends AbstractEntityMeta
{
	/** @see AbstractEntityMeta::DB_TABLE */
	public const DB_TABLE = 'gallery_albums';
	public const DB_TABLE_ALIAS = 'a';

	/** @see AbstractEntityMeta::INSERT_GROUPS */
	protected const INSERT_GROUPS = [Cfg::GRP_ADMINS];
	protected const UPDATE_GROUPS = [Cfg::GRP_ADMINS]; // + просмотр альбомов с 'is_hidden' = 1
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
		'page_id' => [
			'name'          => 'Страница',
			'description'   => 'Страница, на которой размещён альбом.',
			'type'          => FieldInfo::FT_INTEGER,
			'unsigned'      => true,
			'editing_mode'  => FieldInfo::EM_SELECT_FROM_DB,
			'editing_param' => [
				'select' => [['value' => 'id', 'text' => 'CONCAT(name, \' - \', full_path)']],
				'from'   => CPageMeta::DB_TABLE,
				'where'  => "module = '" . PhotoGallery::class . "'",
				'order'  => 'text'
			],
			'required'      => false,
			'search_mode'   => FieldInfo::SM_STRICT,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'parent' => [
			'name'          => 'Родительский альбом',
			'type'          => FieldInfo::FT_INTEGER,
			'unsigned'      => true,
			'editing_mode'  => FieldInfo::EM_SELECT_FROM_DB,
			'editing_param' => [
				'select' => [['value' => 'id', 'text' => 'CONCAT(id, \' - \', name)']],
				'from'   => self::DB_TABLE,
				'order'  => 'text'
			],
			'required'      => false,
			'search_mode'   => FieldInfo::SM_STRICT,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'name' => [
			'name'          => 'Название',
			'type'          => FieldInfo::FT_TEXT,
			'maxlength'     => 255,
			'editing_mode'  => FieldInfo::EM_TEXT,
			'required'      => true,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
 		'cover' => [
			'name'          => 'Обложка',
			'description'   => 'Крайне желательно использовать небольшое оптимизированное изображение (превью). Можно указывать относительный путь.',
			'type'          => FieldInfo::FT_TEXT,
			'maxlength'     => 255,
			'editing_mode'  => FieldInfo::EM_URL,
			'required'      => false,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'content' => [
			'name'          => 'Описание',
			'type'          => FieldInfo::FT_HTML,
			'editing_mode'  => FieldInfo::EM_HTMLEDITOR,
			'required'      => false,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'is_hidden' => [
			'name'          => 'Альбом скрыт',
			'description'   => 'Альбом доступен только Администратору.',
			'type'          => FieldInfo::FT_BOOLEAN,
			'editing_mode'  => FieldInfo::EM_CHECKBOX,
			'required'      => false,
			'default'       => 0,
			'search_mode'   => FieldInfo::SM_BOOLEAN,
			'editable_for'  => [FieldInfo::PERMS_UPDATE_GROUPS]
		],
		'posit' => [
			'name'          => 'Позиция в списке альбомов',
			'description'   => 'Чем меньше, тем выше будет альбом в списке.',
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
			'description'   => 'Дата и время добавления альбома.',
			'type'          => FieldInfo::FT_DATETIME,
			'editing_mode'  => FieldInfo::EM_TEXT,
			'required'      => false,
			'editable_for'	=> []
		],
		'updated_at' => [
			'name'          => 'Время изменения',
			'description'   => 'Дата и время последнего изменения альбома.',
			'type'			=> FieldInfo::FT_DATETIME,
			'required'		=> false,
			'editable_for'	=> []
		]
	];
}
