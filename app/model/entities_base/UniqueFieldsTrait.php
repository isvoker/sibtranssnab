<?php
/**
 * Поддержка уникальности значений полей на уровне конкретной сущностей.
 *
 * @author Dmitriy Lunin
 */
trait UniqueFieldsTrait
{
    /**
     * Проверка уникальности значения полей, составляющих уникальный ключ.
     *
     * @return  bool  Значения уникальны ? TRUE : FALSE
     */
    final public function isUniqueFields(): bool
    {
        return UniqueFieldsProcessorTrait::isUniqueFields($this);
    }
}
