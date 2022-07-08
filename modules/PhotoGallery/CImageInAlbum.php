<?php
/**
 * Реализация класса [Изображение в Альбоме].
 *
 * @author Dmitriy Lunin
 */
class CImageInAlbum extends AbstractEntity
{
	/** Получение данных об альбоме */
	public function pullAlbum(): void {}

	/** Формирование кнопок управления */
	public function buildButtons(): void
	{
		$this->extraData['edit_btns'] = Html::entityEditButtons(
			__CLASS__,
			[Action::UPDATE, Action::DELETE],
			[
				'id' => $this->id,
				'userGroups' => CImageInAlbumMeta::getPermissions(Action::UPDATE)
			]
		);
	}

	/** @see AbstractEntity::buildExtraData() */
	public function buildExtraData(): void
	{
		$this->buildButtons();

		if (User::isAdmin()) {
			$this->extraData['update_link'] = BlockManager::getAdminBlockPath(PhotoGallery::class) . "&section=images&mode=update&album_id={$this->album_id}&id={$this->id}";
			$this->extraData['admin_image_resized'] = FsImage::safeResize($this->img_path, ['dstW' => 150]);
		}

		$this->extraData['file_name'] = basename($this->img_path);
		$this->extraData['img_th_path'] = FsImage::safeResize($this->img_path, ['dstW' => 500]);
	}

	/**
	 * Получение кода кнопки добавления объекта.
	 *
	 * @param   int|string  $albumId  ID альбома
	 * @return  string
	 */
	public static function getInsertButton($albumId): string
	{
		return Html::entityEditButtons(
			'CImageInAlbum',
			[Action::INSERT],
			[
				'userGroups' => CImageInAlbumMeta::getPermissions(Action::INSERT),
				'fields' => ['album_id' => $albumId]
			]
		);
	}
}
