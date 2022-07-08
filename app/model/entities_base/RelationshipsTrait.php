<?php
/**
 * Поддержка связей ManyToMany и OneToMany с другими сущностями
 * для контроллеров сущностей.
 *
 * @author Dmitriy Lunin
 */
trait RelationshipsTrait
{
    /** Поля связанных сущностей, сгруппированные по классам */
    protected $rel_objects = [];

    /**
     * Получение полей всех или заданных объектов.
     *
     * @param  ?string  $relClassName  Класс связанной сущности
     * @return  array
     */
    final public function getRelObjects(?string $relClassName = null): array
    {
        if ($relClassName !== null) {
            if (!isset($this->rel_objects[ $relClassName ])) {
                throw new InvalidArgumentException("Relationship with `{$relClassName}` is not defined");
            }
            return $this->rel_objects[ $relClassName ];
        }
        return $this->rel_objects;
    }

    /**
     * Удаление полей сущностей заданного класса.
     *
     * @param  string  $relClassName  Класс связанной сущности
     */
    final public function removeRelObjects(string $relClassName): void
    {
        $this->rel_objects[ $relClassName ] = [];
    }

    /**
     * Обработка доп. полей формы редактирования объекта для связи ManyToMany:
     * - получение списка ID связанных объектов.
     *
     * @param   array   $fields        Поля формы
     * @param   string  $relClassName  Класс связанной сущности
     * @param   array   $rel           Описание связи
     * @return  array
     */
    final protected static function extractFieldsManyToMany(
        array $fields,
        string $relClassName,
        array $rel
    ): array {
        if (isset($fields["rel_with[{$relClassName}]"])) {
            $relWith = $fields["rel_with[{$relClassName}]"];
            if (is_array($relWith)) {
                return array_filter($relWith, 'is_numeric');
            }
            if (is_numeric($relWith)) {
                return [ $relWith ];
            }
        }
        return [];
    }

    /**
     * Обработка доп. полей формы редактирования объекта для связи OneToMany:
     * - получение полей связанных объектов.
     *
     * @param   array   $fields        Поля формы
     * @param   string  $relClassName  Класс связанной сущности
     * @param   array   $rel           Описание связи
     * @return  array
     */
    final protected static function extractFieldsOneToMany(
        array $fields,
        string $relClassName,
        array $rel
    ): array {
        return extractObjFields($fields, $rel['fields_prefix']);
    }

    /**
     * Обработка доп. полей формы редактирования объекта.
     *
     * @param  array  $fields  Поля формы
     */
    final public function setExtraFields(array $fields): void
    {
        foreach ($this->getMetaInfo()->getMapping() as $relClassName => $rel) {
            if ($rel['relation_type'] === 'ManyToMany') {
                $this->rel_objects[ $relClassName ] =
                    self::extractFieldsManyToMany($fields, $relClassName, $rel);
            } else if ($rel['relation_type'] === 'OneToMany') {
                $this->rel_objects[ $relClassName ] =
                    self::extractFieldsOneToMany($fields, $relClassName, $rel);
            }
        }
    }
}
