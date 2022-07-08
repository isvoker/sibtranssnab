<?php
/**
 * Базовая реализация класса [[CBlock]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CBlock]].
 *
 * @author Dmitriy Lunin
 */
class BaseCBlock extends AbstractEntity
{
	/** Формирование кнопок управления */
	public function buildButtons(): void
	{
		$this->extraData['edit_btns'] = Html::entityEditButtons(
			'CBlock',
			[Action::UPDATE, Action::DELETE],
			[
			    'id' => $this->id,
                'userGroups' => CBlockMeta::getPermissions(Action::UPDATE)
            ]
		);
	}

    /** @see AbstractEntity::buildExtraData() */
	public function buildExtraData(): void
	{
		$this->buildButtons();
	}

	/**
	 * Получение кода кнопки добавления объекта.
	 *
	 * @return string
	 */
	public static function getInsertButton(): string
	{
		return Html::entityEditButtons(
			'CBlock',
			[Action::INSERT],
			['userGroups' => CBlockMeta::getPermissions(Action::INSERT)]
		);
	}
}
