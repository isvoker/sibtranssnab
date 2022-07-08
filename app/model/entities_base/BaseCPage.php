<?php
/**
 * Базовая реализация класса [[CPage]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CPage]].
 *
 * @author Dmitriy Lunin
 */
class BaseCPage extends AbstractEntity
{
    /**
     * Обработка доп. полей формы редактирования объекта:
     * - получение желаемой позиции страницы;
     * - получение прав доступа.
     *
     * @param  array  $fields
     */
    public function setExtraFields(array $fields): void
    {
        if (is_numeric($fields['posit_after'] ?? null)) {
            $this->extraData['posit_target'] = $fields['posit_after'];
            $this->extraData['posit_point'] = 'bottom';
        }

        $this->extraData['perms'] = parsePermissions($fields);
    }

    /** Получение прав доступа для групп пользователей */
    public function pullPerms(): void
    {
        $this->extraData['perms'] = [];

        if (!$this->id) {
            return;
        }

        $perms = DBCommand::select([
            'select' => 'group_id, statuses',
            'from'   => CPageMeta::getDBTablePermissions(),
            'where'  => 'page_id = ' . DBCommand::qV($this->id)
        ]);
        foreach ($perms as $pair) {
            $this->extraData['perms'][ $pair['group_id'] ] = $pair['statuses'];
        }
    }

    /** @see AbstractEntity::buildExtraData() */
    public function buildExtraData(): void
    {
        $this->pullPerms();
    }
}
