<?php
/**
 * Итератор объектов [[FieldInfo]].
 *
 * @author Lunin Dmitriy
 */
class FieldInfoIterator implements Iterator
{
    /**
     * Массив [[$fieldsMeta]].
     *
     * @var array
     */
    protected $data = [];

    /**
     * Массив списков групп, которым разрешены операции над [Сущностью]:
     * [INSERT_GROUPS, UPDATE_GROUPS, DELETE_GROUPS].
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Список групп пользователей, от лица которых запрашиваются данные.
     *
     * @var array
     */
    protected $forUserGroups = [];

    /**
     * FieldInfoIterator constructor.
     *
     * @param  array  $data
     * @param  array  $permissions
     * @param  array  $forUserGroups
     */
    public function __construct(
        array $data,
        array $permissions = [],
        array $forUserGroups = []
    ) {
        $this->data = $data;
        $this->permissions = $permissions;
        $this->forUserGroups = $forUserGroups;
    }

    /**
     * Возврат текущего элемента.
     *
     * @return FieldInfo
     * @throws InternalEx
     */
    public function current(): FieldInfo
    {
        return new FieldInfo(
            current($this->data),
            $this->permissions,
            $this->forUserGroups
        );
    }

    /**
     * Возврат ключа текущего элемента.
     *
     * @return ?string
     */
    public function key(): ?string
    {
        return key($this->data);
    }

    /**
     * Переход к следующему элементу.
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Перемотка итератора на первый элемент.
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * Проверка корректности текущей позиции.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
    }
}
