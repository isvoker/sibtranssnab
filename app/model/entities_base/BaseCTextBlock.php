<?php
/**
 * Базовая реализация класса [[CTextBlock]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CTextBlock]].
 *
 * @author Dmitriy Lunin
 */
class BaseCTextBlock extends AbstractEntity
{
	/** Формирование кнопок управления */
	public function buildButtons(): void
	{
		$this->extraData['edit_btns'] = Html::entityEditButtons(
			'CTextBlock',
			[Action::UPDATE, Action::DELETE],
			[
			    'id' => $this->id,
                'userGroups' => CTextBlockMeta::getPermissions(Action::UPDATE)
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
			'CTextBlock',
			[Action::INSERT],
			['userGroups' => CTextBlockMeta::getPermissions(Action::INSERT)]
		);
	}
}
