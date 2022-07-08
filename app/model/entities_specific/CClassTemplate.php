<?php
/**
 * Реализация класса [[CClassTemplate]].
 *
 * @author Dmitriy Lunin
 */
class CClassTemplate extends AbstractEntity
{
    use StatusesTrait;

    /** Формирование кнопок управления */
    public function buildButtons(): void
    {
        $this->extraData['edit_btns'] = Html::entityEditButtons(
            'CClassTemplate',
            [Action::UPDATE, Action::DELETE],
            [
                'id' => $this->id,
                'userGroups' => CClassTemplateMeta::getPermissions(Action::UPDATE)
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
            'CClassTemplate',
            [Action::INSERT],
            ['userGroups' => CClassTemplateMeta::getPermissions(Action::INSERT)]
        );
    }
}
