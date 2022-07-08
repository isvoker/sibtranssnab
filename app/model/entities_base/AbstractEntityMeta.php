<?php
/**
 * Базовый класс метаинформации [Сущности].
 *
 * @author Pavel Nuzhdin <pnzhdin@gmail.com>
 * @author Dmitriy Lunin
 */
abstract class AbstractEntityMeta
{
    /** Таблица [Сущности] в БД и её псевдоним */
    public const DB_TABLE = '';
    public const DB_TABLE_ALIAS = '';

    /** Массив полей, составляющих первичный ключ */
    public const PRIMARY_KEY = ['id'];

    /** Список полей [Сущности], значение которых следует скрывать */
    public const SECRET_FIELDS = [];

    /**
     * Списки групп пользователей, которым разрешено
     * добавлять/изменять/удалять [Сущность].
     */
    protected const INSERT_GROUPS = [];
    protected const UPDATE_GROUPS = [];
    protected const DELETE_GROUPS = [];

    /**
     * Возможное значение первого элемента INSERT_GROUPS, UPDATE_GROUPS и DELETE_GROUPS,
     * указывающее, что ограничений по группам нет.
     */
    public const PERMS_ALL_GROUPS = 'ALL';

    /**
     * Массив, ключи которого представляют перечисление тех действий
     * с [Сущностью], которые требуют более сложной проверки,
     * чем принадлежность к списку групп.
     * Такие операции должны обрабатываться специальным методом и передаваться
     * EntityManager с $isTrusted = true.
     */
    protected const ACTIONS_TO_BE_TRUSTED = [
        //Action::INSERT => true,
        //Action::UPDATE => true,
        //Action::DELETE => true,
    ];

    /**
     * ИММУТАБЕЛЬНАЯ метаинформация о полях [Сущности].
     * Список, в котором ключи - названия полей,
     * а элементы - ассоциативные массивы со значениями свойств поля:
     * ! string  name - имя;
     *   string  description - описание;
     * ! string  type - тип;
     * ! bool    required - значение не должно быть пустым;
     *   bool    unsigned - только неотрицательные значения;
     *   int     maxlength - максимальная длина (для текстовых полей);
     *   int     precision - допустимое кол-во десятичных знаков (для полей типа float);
     *   string  dic - имя словаря (для полей со списком допустимых строковых значений);
     *   int     dic_multi_max - максимальное число вариантов при выборе из словаря;
     *   string  editing_mode - способ редактирования;
     *   mixed   editing_param - параметры редактирования (для EM_SELECT_FROM_DB и EM_AUTOCOMPLETE);
     *   mixed   default - значение по умолчанию;
     *   string  search_mode - способ поиска по полю (нет свойства - нет поиска);
     *   array   visible_for - если задано, поле видят только перечисленные роли;
     *   array   editable_for - если задано, поле редактируют только перечисленные роли;
     *   string  regexp - регулярное выражение для валидации значения;
     *   string  encode_from - название поля, кодированное значение которого будет значением по умолчанию;
     *   int     morethen_value - нижний порог числового значения данного поля;
     *   bool    history_ignore - игнорировать ли изменение значения при записи в историю;
     *   string  fieldset - названия группы поля (для формы редактирования);
     * (! - обязательно)
     */
    protected $fields = [];

    /** Singleton instances for each class */
    private static $instances = [];

    /** Запрет создания экземпляров через new */
    final private function __construct() {}

    /** Запрет создания экземпляров через клонирование */
    final private function __clone() {}

    /**
     * Создание экземпляра класса метаинформации.
     *
     * @param   string  $className  Имя класса
     * @return  AbstractEntityMeta
     */
    private static function newInstance(string $className): AbstractEntityMeta
    {
        if (!class_exists($className, false)) {
            throw new InternalEx("Class `{$className}` not found");
        }

        return new $className();
    }

    /**
     * Получение единственного однажды созданного экземпляра класса метаинформации.
     *
     * @return AbstractEntityMeta
     */
    final public static function getInstance(): AbstractEntityMeta
    {
        if (empty(self::$instances[ static::class ])) {
            if (!is_subclass_of(static::class, 'AbstractEntityMeta')) {
                throw new InternalEx('This class does not support method `getInstance()`');
            }

            self::$instances[ static::class ] = self::newInstance(static::class);
        }

        return self::$instances[ static::class ];
    }

    /**
     * Получение названия таблицы в БД.
     *
     * @return string
     */
    public static function getDBTable(): string
    {
        return Cfg::DB_TBL_PREFIX . static::DB_TABLE;
    }

    /**
     * Получение псевдонима таблицы в БД.
     *
     * @return string
     */
    public static function getDBTableAlias(): string
    {
        return Cfg::DB_TBL_PREFIX . static::DB_TABLE_ALIAS;
    }

    /**
     * Получение метаинформации полей [Сущности] "как есть".
     *
     * @return array[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Получение метаинформации заданного поля.
     *
     * @param   string  $field  Имя поля
     * @return  FieldInfo
     * @throws  InternalEx
     */
    public function getFieldInfo(string $field): FieldInfo
    {
        if (isset($this->fields[ $field ])) {
            return new FieldInfo($this->fields[ $field ]);
        }

        throw new InvalidArgumentException('Trying to get undefined [[FieldInfo]]');
    }

    /**
     * Получение метаинформации полей [Сущности] в виде списка [[FieldInfo]].
     *
     * @param   array  $forUserGroups  Список групп пользователей, от лица которых запрашиваются данные
     * @return  FieldInfoIterator
     */
    public function getFieldsInfo(array $forUserGroups = []): FieldInfoIterator
    {
        return new FieldInfoIterator(
            $this->fields,
            [
                static::INSERT_GROUPS,
                static::UPDATE_GROUPS,
                static::DELETE_GROUPS
            ],
            $forUserGroups
        );
    }

    /**
     * Получение метаинформации полей [Сущности] в виде списка [[FieldInfo]]
     * от лица текущего пользователя.
     *
     * @return FieldInfoIterator
     */
    public function getFieldsInfoForMe(): FieldInfoIterator
    {
        return $this->getFieldsInfo( array_keys(User::getGroups()) );
    }

    /**
     * Получение списка ролей, которым разрешено выполнять с [Сущностью] заданное действие.
     *
     * @param   string  $action  [ Action::INSERT | Action::UPDATE | Action::DELETE ]
     * @return  string[]
     */
    final public static function getPermissions(string $action): array
    {
        if (
            $action !== Action::INSERT
            && $action !== Action::UPDATE
            && $action !== Action::DELETE
        ) {
            return [];
        }

        $action = strtoupper($action);

        return constant("static::{$action}_GROUPS");
    }

    /**
     * Разрешено ли совершать действие с [Сущностью]
     * без дополнительной проверка прав пользователя,
     * помимо принадлежности к некоторой группе?
     *
     * @see     AbstractEntityMeta::ACTIONS_TO_BE_TRUSTED
     * @param   string  $action  [ Action::INSERT | Action::UPDATE | Action::DELETE ]
     * @return  bool
     */
    final public static function isActionMustBeTrusted(string $action): bool
    {
        return isset(static::ACTIONS_TO_BE_TRUSTED[ $action ]);
    }

    /**
     * Разрешено ли участникам групп, в которых состоит пользователь,
     * совершать действие с [Сущностью]?
     *
     * @param   string  $action     [ Action::INSERT | Action::UPDATE | Action::DELETE ]
     * @return  bool
     */
    final public static function canWeDoIt(string $action): bool
    {
        $groups = static::getPermissions($action);

        return !empty($groups)
            && (
                $groups[0] === self::PERMS_ALL_GROUPS
                || User::isInGroup($groups, false)
            );
    }

    /**
     * Позволено ли пользователю совершить действие с [Сущностью]?
     *
     * @param   string  $action     [ Action::INSERT | Action::UPDATE | Action::DELETE ]
     * @param   bool    $isTrusted  Дополнительная проверка прав пользователя уже выполнена
     * @return  bool
     */
    final public static function canIDoThis(string $action, bool $isTrusted = false): bool
    {
        if ($isTrusted) {
            return true;
        }

        if (self::isActionMustBeTrusted($action)) {
            return false;
        }

        return self::canWeDoIt($action);
    }

    /**
     * @return array
     * @deprecated
     * @use $Meta::PRIMARY_KEY
     */
    public static function getDBPK(): array
    {
        trigger_error(
            'The AbstractEntityMeta::getDBPK() method is no longer supported',
            E_USER_DEPRECATED
        );
        return static::PRIMARY_KEY;
    }

    /**
     * @return array
     * @deprecated
     * @use $Meta::SECRET_FIELDS
     */
    public function getHiddenFields(): array
    {
        trigger_error(
            'The AbstractEntityMeta::getHiddenFields() method is no longer supported',
            E_USER_DEPRECATED
        );
        return static::SECRET_FIELDS;
    }
}
