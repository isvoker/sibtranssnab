<?php
/**
 * Поддержка связей ManyToMany и OneToMany с другими сущностями
 * для классов метаинформации сущностей.
 *
 * @author Dmitriy Lunin
 */
trait RelationshipsMetaTrait
{
    /** Шаблон описания связей с другими сущностями */
    /*
    protected static $MAPPING = [
        '<CRelatedManyToMany>' => [
            'relation_type' => 'ManyToMany',
            'is_hidden' => true, // не добавлять форму редактирования в getHtmlForSingleObjForm(); NOT required>
            'editable_for' => '<аналогично метаинформации поля; NOT required>',
            'name' => '<название-совокупности-связанных-сущностей>',
            'table' => DB_TBL_PREFIX . '<table-name>',
            'join' => ['column' => '<class>_id', 'reference' => 'id', 'label' => 'name'],
            'inverse_join' => ['column' => '<rel_class>_id', 'reference' => 'id', 'label' => 'name'],
            {'manager' => ''} // если подключать соответствующий "Manager" не нужно
        ],
        '<CRelatedOneToMany>' => [
            'relation_type' => 'OneToMany',
            'is_hidden' => true, // не добавлять форму редактирования в getHtmlForSingleObjForm(); NOT required>
            'editable_for' => '<аналогично метаинформации поля; NOT required>',
            'name' => '<название-совокупности-связанных-сущностей>',
            'field' => 'id',
            'referenced_field' => '<class>_id', // связующее поле в зависимых сущностях
            'required_field' => 'name', // поле, по которому проверяется добавление/удаление
            {'manager' => ''} // если подключать соответствующий "Manager" не нужно
        ],
    ];
    */

    /** Дополнение описания связей типовыми данными; подключение Manager'ов */
    final protected static function prepareMapping(): void
    {
        foreach (static::$MAPPING as $relClassName => &$rel) {
            if (isset($rel['manager'])) {
                return;
            }

            $rel['manager'] = substr($relClassName, 1) . 'Manager';

            if ($rel['relation_type'] === 'OneToMany') {
                $rel['fields_prefix'] = '_' . ($relClassName . 'Meta')::DB_TABLE_ALIAS . '_';
            }
        }
    }

    /**
     * Получение описания связи с заданной сущностью или всех связей,
     * если нужный класс не задан.
     *
     * @param  ?string  $relClassName  Класс связанной сущности
     * @param   bool    $wPreparation  Необходимо предварительно выполнить [[self::prepareMapping()]]
     * @return  array
     */
    final public static function getMapping(?string $relClassName = null, bool $wPreparation = true): array
    {
        if ($wPreparation) {
            self::prepareMapping();
        }

        if ($relClassName !== null) {
            if (!isset(static::$MAPPING[ $relClassName ])) {
                throw new InvalidArgumentException("Relationship with `{$relClassName}` is not defined");
            }
            return static::$MAPPING[ $relClassName ];
        }

        return static::$MAPPING;
    }

    /**
     * Получение описания связей с добавленным флагом 'is_editable'
     * для заданного набора групп пользователей.
     * Связь считается редактируемой, если:
     * !( <ограничения заданы> И ( <ограничения пусты> ИЛИ <пользователь не состоит в группах 'editable_for'> ) )
     *
     * @param   array  $userGroups    Группы пользователей. По умолчанию - группы текущего пользователя.
     * @param   bool   $wPreparation  @see [[self::getMapping()]]
     * @return  array
     */
    final public static function getMappingWithPermissions(
        array $userGroups = [],
        bool $wPreparation = true
    ): array {
        $mapping = self::getMapping(null, $wPreparation);
        if (empty($mapping)) {
            return [];
        }

        if (empty($userGroups)) {
            $userGroups = array_keys(User::getGroups());
        }
        foreach ($mapping as $relClassName => &$rel) {
            $rel['is_editable'] = !(
                isset($rel['editable_for'])
                && (
                    empty($rel['editable_for'])
                    || ( $rel['editable_for'][0] !== AbstractEntityMeta::PERMS_ALL_GROUPS
                        && !array_intersect($rel['editable_for'], $userGroups) )
                )
            );
        }

        return $mapping;
    }
}
