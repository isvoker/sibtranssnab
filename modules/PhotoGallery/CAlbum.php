<?php
/**
 * Реализация класса [Альбом].
 *
 * @author Dmitriy Lunin
 */
class CAlbum extends AbstractEntity
{
	/** Формирование кнопок управления */
	public function buildButtons(): void
	{
		$this->extraData['edit_btns'] = Html::entityEditButtons(
			'CAlbum',
			[Action::UPDATE, Action::DELETE],
			[
				'id' => $this->id,
				'userGroups' => CAlbumMeta::getPermissions(Action::UPDATE)
			]
		);
	}

	/** @see AbstractEntity::buildExtraData() */
	public function buildExtraData(): void
	{
		$this->buildButtons();

		$this->extraData['url'] = AlbumManager::makeAlbumUrl($this->id);
	}

	/**
	 * Получение кода кнопки добавления объекта.
	 *
	 * @param  int|string  $pageId  ID родительской страницы
	 * @return string
	 */
	public static function getInsertButton($pageId): string
	{
		return Html::entityEditButtons(
			'CAlbum',
			[Action::INSERT],
			[
				'userGroups' => CAlbumMeta::getPermissions(Action::INSERT),
				'fields' => ['page_id' => $pageId]
			]
		);
	}

	public function isHidden() {
		return (bool)$this->is_hidden;
	}
}
