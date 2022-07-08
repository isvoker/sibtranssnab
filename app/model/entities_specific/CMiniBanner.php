<?php

/**
 * Контроллер сущности [[CMiniBanner]].
 *
 */
class CMiniBanner extends AbstractEntity
{
    /** Формирование кнопок управления */
    public function buildButtons()
    {
        $this->extraData['edit_btns'] = Html::entityEditButtons(
            'CMiniBanner',
            [Action::UPDATE, Action::DELETE],
            ['id' => $this->id, 'userGroups' => CMiniBannerMeta::getPermissions(Action::UPDATE)]
        );
    }

    /** @see [[AbstractEntity]] */
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
            'CMiniBanner',
            [Action::INSERT],
            ['userGroups' => CMiniBannerMeta::getPermissions(Action::INSERT)]
        );
    }

    public function __toString():string
    {
        $name = 'Мини баннер';
        if (isset($this->title)) {
            $name .= ' "' . $this->title . '"';
        }
        return $name;
    }
}
