<?php

ClassLoader::loadClass('AbstractEntityMeta');
ClassLoader::loadClass('AbstractEntity');
ClassLoader::loadClass('EntityNotFoundEx');

/**
 * Управление Сущностями.
 *
 * @author Lunin Dmitriy
 */
class EntityManager
{
    /**
     * Если для заданного класса создан специальный класс "Manager",
     * будет вызван заданный метод этого Manager'а.
     * Иначе будет использован [[EntityManager]].
     *
     * @param   string  $className     Класс (например, 'CClassTemplate')
     * @param   string  $method        Вызываемый метод Manager'а
     * @param   mixed   $args[, ... ]  Аргументы вызываемого метода
     * @return  mixed
     */
    final public static function useBetterManager(string $className, string $method, ...$args)
    {
        $manager = substr($className, 1) . 'Manager';

	    if (!class_exists($manager)) {
            $manager = 'EntityManager';
        }

        if (method_exists($manager, $method)) {
            return call_user_func_array([$manager, $method], $args);
        }

        throw new InternalEx("Call to undefined method `{$manager}::{$method}()`");
    }

    /**
     * Создание объекта заданного класса.
     *
     * @param   string         $className  Класс
     * @param   array          $fields     Поля объекта и их значения
     * @param  ?ObjectOptions  $Options    Параметры создания объекта :
     * ~~~
     *   array  $fields         = []
     *   bool   $withExtraData  = false
     *   bool   $forOutput      = false
     *   bool   $skipValidation = false
     *   bool   $showSensitive  = false
     * ~~~
     * @return  AbstractEntity
     */
    final public static function createObject(
        string $className,
        array $fields = [],
        ObjectOptions $Options = null
    ): AbstractEntity {
        ClassLoader::loadClass($className);

        return new $className($fields, $Options);
    }

    /**
     * Проверка того, является ли объект экземпляром указанного класса.
     *
     * @param  AbstractEntity  $Object     Объект
     * @param  string          $className  Класс
     */
    final public static function checkObjectType(
        AbstractEntity $Object,
        string $className
    ): void {
        if (!($Object instanceof $className)) {
            throw new InvalidArgumentException("Argument must be an instance of `{$className}`");
        }
    }

    /**
     * Создание списка объектов на основе групп полей, полученного выборкой из БД.
     *
     * @param   array          $dbRows     Список, полученный выборкой из БД
     * @param   string         $className  Имя класса, объекты которого будут порождаться
     * @param  ?ObjectOptions  $Options    Параметры создания объекта :
     * ~~~
     *   bool  $withExtraData  = false
     *   bool  $forOutput      = false
     *   bool  $skipValidation = false
     *   bool  $showSensitive  = false
     * ~~~
     * @return  AbstractEntity[]
     */
    final public static function baseToObjects(
        array $dbRows,
        string $className,
        ObjectOptions $Options = null
    ): array {
        if (empty($dbRows)) {
            return [];
        }

        is_null($Options) && $Options = new ObjectOptions();

        $ObjList = [];
        foreach ($dbRows as $fields) {
            $ObjList[] = new $className( $fields, $Options );
        }

        return $ObjList;
    }

    /**
     * Генерация набора критериев поиска в формате [[DBQueryBuilder::where]] #2
     * для выборки записи по первичному ключу.
     *
     * @param   AbstractEntity      $Object  Объект с заданными значениями полей, составляющих первичный ключ
     * @param  ?AbstractEntityMeta  $Meta    Метаинформация
     * @return  array
     */
    final public static function buildPKRestricts(
        AbstractEntity $Object,
        AbstractEntityMeta $Meta = null
    ): array {
        if (!($Meta instanceof AbstractEntityMeta)) {
            $Meta = $Object->getMetaInfo();
        }

        $where = [];
        foreach ($Meta::PRIMARY_KEY as $field) {
            if (is_null($Object->fields[ $field ])) {
                throw new InternalEx('Field or set of fields from primary key is empty');
            }

            $where[] = [
                'oper' => 'AND',
                'clause' => ":{$field}: = {value}",
                'values' => [ $Object->fields[ $field ] ]
            ];
        }

        if (empty($where)) {
            throw new InternalEx('Primary key constraints is empty');
        }

        return $where;
    }

    /**
     * Генерация набора критериев поиска в формате [[DBCommand::where]] #2
     * для выборки записей из БД.
     *
     * @param   AbstractEntityMeta  $Meta       Метаинформация
     * @param   array               $restricts  Параметры поиска вида: ['fldName1' => 'val1', ...]
     * @return  array
     */
    final public static function baseBuildRestricts(
        AbstractEntityMeta $Meta,
        array $restricts
    ): array {
        if (empty($restricts)) {
            return [];
        }

        $FetchBy = new FetchBy();
        $FetchBy->and($restricts);

        return $FetchBy->buildWhereFromMeta($Meta);
    }

    /**
     * Добавление объекта в БД.
     * Поля со значением NULL игнорируются.
     * Если PK состоит из одного поля, считается, что это поле с атрибутом AUTO_INCREMENT,
     * его значение не учитывается и будет заменено на присвоенное БД.
     * Проверка прав на редактирование каждого заполненного поля не выполняется.
     *
     * @param   AbstractEntity  $Object     Добавляемый объект
     * @param   bool            $isTrusted  Проверка прав пользователя уже выполнена
     * @return  AbstractEntity  Добавленный объект
     */
    public static function add(AbstractEntity $Object, bool $isTrusted = false): AbstractEntity
    {
        $Meta = $Object->getMetaInfo();

        if (!$Meta::canIDoThis(Action::INSERT, $isTrusted)) {
            throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
        }

        if (($emptyField = $Object->getEmptyRequiredField()) !== null) {
            throw new EntityEditEx(EntityEditEx::FIELD_IS_EMPTY, $emptyField);
        }

        $metaPK = $Meta::PRIMARY_KEY;
        if (isset($metaPK[0]) && !isset($metaPK[1])) {
            $fieldAutoInc = $metaPK[0];
        } else {
            $fieldAutoInc = null;
        }

        $insertData = [];
        foreach ($Meta->getFieldsInfoForMe() as $field => $FieldInfo) {
            if ($field !== $fieldAutoInc) {
                $value = $Object->fields[ $field ];

                if ($field === 'posit' && is_callable('static::setPosit')) {
                    $isEditable = $FieldInfo->isEditable();
                    if (
                        ($isEditable && !$value)
                        || (!$isEditable && !$Object->canTrust($field))
                    ) {
                        static::setPosit($Object);
                        $insertData[ $field ] = $Object->fields[ $field ];
                    }
                } else {
                    $value = AbstractEntity::validateFieldValue($FieldInfo, $value);
                    if ($value !== null) {
                        $insertData[ $field ] = AbstractEntity::makeValueForStorage($FieldInfo, $value);
                    }
                }
            }
        }

        $insertId = DBCommand::insert($Meta::getDBTable(), $insertData);
        if ($insertId !== 0 && $fieldAutoInc !== null) {
            $Object->fields[ $fieldAutoInc ] = $insertId;
        }

        return $Object;
    }

    /**
     * Обновление объекта в БД.
     * Поля с типом AbstractEntityMeta::FT_FTS не обновляются.
     * UPDATE выполняется только для изменённых полей.
     *
     * @param   AbstractEntity  $Object     Обновляемый объект
     * @param   AbstractEntity  $NewObject  Объект с новыми данными
     * @param   bool            $isTrusted  Проверка прав пользователя уже выполнена
     * @return  AbstractEntity  Обновлённый объект
     */
    public static function update(
        AbstractEntity $Object,
        AbstractEntity $NewObject,
        bool $isTrusted = false
    ): AbstractEntity {
        $Meta = $Object->getMetaInfo();

        if (!$Meta::canIDoThis(Action::UPDATE, $isTrusted)) {
            throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
        }

        $updateData = [];
        foreach ($Meta->getFieldsInfoForMe() as $field => $FieldInfo) {
            $value = $NewObject->fields[ $field ];

            if (
                $Object->fields[ $field ] != $value
                && !$FieldInfo->typeIsFts()
                && ($FieldInfo->isEditable() || $NewObject->canTrust($field))
            ) {
                $value = AbstractEntity::validateFieldValue($FieldInfo, $value);

                if (
                    (is_null($value) || $value === '')
                    && $FieldInfo->isRequired()
                ) {
                    throw new EntityEditEx(EntityEditEx::FIELD_IS_EMPTY, $FieldInfo->getName());
                }

                $Object->fields[ $field ] = $value;
                $updateData[ $field ] = AbstractEntity::makeValueForStorage($FieldInfo, $value);
            }
        }

        if (!empty($updateData)) {
            DBCommand::update(
                $Meta::getDBTable(),
                $updateData,
                self::buildPKRestricts($Object, $Meta)
            );
        }

        return $Object;
    }

    /**
     * Удаление объекта из БД.
     *
     * @param  AbstractEntity  $Object     Удаляемый объект
     * @param  bool            $isTrusted  Проверка прав пользователя уже выполнена
     */
    public static function delete(AbstractEntity $Object, bool $isTrusted = false): void
    {
        $Meta = $Object->getMetaInfo();

        if (!$Meta::canIDoThis(Action::DELETE, $isTrusted)) {
            throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
        }

        DBCommand::delete(
            $Meta::getDBTable(),
            self::buildPKRestricts($Object, $Meta)
        );
    }

    /**
     * Выборка полей объекта из БД.
     *
     * @param  AbstractEntity  $Object      Объект с заданными значениями полей, составляющих первичный ключ
     * @param ?ObjectOptions   $Options     Параметры создания объекта :
     * ~~~
     *   bool  $withExtraData  = false
     *   bool  $forOutput      = false
     *   bool  $showSensitive  = false
     * ~~~
     * @param  string          $notFoundEx  Класс исключения типа [[EntityNotFoundEx]]
     */
    public static function select(
        AbstractEntity $Object,
        ObjectOptions $Options = null,
        string $notFoundEx = ''
    ): void {
        $Meta = $Object->getMetaInfo();
        $fields = DBCommand::select([
            'from'  => DBCommand::qC( $Meta::getDBTable() ),
            'where' => self::buildPKRestricts($Object, $Meta)
        ], DBCommand::OUTPUT_FIRST_ROW);

        if (empty($fields)) {
            if ($notFoundEx === '' || !class_exists($notFoundEx)) {
                $notFoundEx = 'EntityNotFoundEx';
            }
            throw new $notFoundEx();
        }

        is_null($Options) && $Options = new ObjectOptions();
        $Options->setSkipValidation();

        $Object->setFields($fields, $Options);

        if ($Options->getWithExtraData()) {
            $Object->buildExtraData();
        }
        if ($Options->getForOutput()) {
            $Object->buildFieldsForOutput();
        }
    }

    /**
     * Возвращает объект по идентификатору.
     *
     * @param   string         $className   Класс объекта
     * @param   string         $notFoundEx  Класс исключения типа [[EntityNotFoundEx]]
     * @param   int            $id          ID записи в БД
     * @param  ?ObjectOptions  $Options     Параметры создания объекта :
     * ~~~
     *   bool  $withExtraData  = false
     *   bool  $forOutput      = false
     *   bool  $showSensitive  = false
     * ~~~
     * @return  AbstractEntity
     */
    final public static function baseGetById(
        string $className,
        string $notFoundEx,
        int $id,
        ObjectOptions $Options = null
    ): AbstractEntity {
        $Object = new $className(['id' => $id]);
        self::select($Object, $Options, $notFoundEx);

        return $Object;
    }

    /**
     * Получение максимального значения идентификатора из используемых.
     *
     * @param   AbstractEntityMeta  $Meta     Метаинформация
     * @return  int
     */
    final public static function getLastId(AbstractEntityMeta $Meta): int
    {
        return DBCommand::select([
            'select' => 'MAX(id)',
            'from'   => DBCommand::qC( $Meta::getDBTable() )
        ], DBCommand::OUTPUT_FIRST_CELL);
    }

    /**
     * Проверка корректности параметров сортировки и корректировка при необходимости.
     *
     * @param   AbstractEntityMeta  $Meta     Метаинформация
     * @param   mixed               $orderBy  Параметры сортировки вида ['fieldName' => 'ASC'|'DESC', ...] или строка
     * @return  array|string
     */
    final public static function validateOrderBy(AbstractEntityMeta $Meta, $orderBy)
    {
        if (is_string($orderBy)) {
            if ($orderBy === DBQueryBuilder::RAND) {
                return $orderBy;
            }

            $expressions = explode(',', $orderBy);
            $orderBy = [];
            foreach ($expressions as $i => $expr) {
                $expr = explode(' ', clearWhiteSpaces($expr));
                $orderBy[ $expr[0] ] = $expr[1] ?? true;
            }
            unset($expressions);
        }

        if (empty($orderBy) || !is_array($orderBy)) {
            return [];
        }

        $fieldsMeta = $Meta->getFields();
        $maxColPosition = count($fieldsMeta);

        foreach ($orderBy as $column => $direction) {
            if (
                isset($fieldsMeta[ $column ])
                || (is_numeric($column) && $column > 0 && $column <= $maxColPosition)
            ) {
                continue;
            }
            unset($orderBy[ $column ]);
        }

        return $orderBy;
    }

    /**
     * Возвращает список объектов.
     *
     * @param  AbstractEntityMeta  $Meta       Метаинформация
     * @param  array               $restricts  Параметры поиска
     * @param  array               $options    Доп. параметры:
     * ~~~
     *   bool   $count     Надо ли посчитать общее кол-во подходящих объектов
     *   array  $fields    Список требуемых полей. Если не задано, выбираются все.
     *   mixed  $orderBy   Параметры сортировки вида ['fieldName' => 'ASC'|'DESC', ...] или строка
     *   int    $limit     Макс. кол-во возвращаемых объектов
     *   int    $offset    Начиная с какой записи в БД искать
     *   bool   $lazyLoad  Можно ли НЕ подгружать зависимые записи?
     *   bool   $forPrint  Создавать представление полей, подходящее для отображения?
     * ~~~
     * @return  array
     */
    final public static function baseFind(
        AbstractEntityMeta $Meta,
        array $restricts = [],
        array $options = []
    ): array {
        $FetchBy = (new FetchBy())
            ->and($restricts);

        $FetchOptions = (new FetchOptions())
            ->setCount($options['count'] ?? false)
            ->setSelect($options['fields'] ?? [])
            ->setOrderBy($options['orderBy'] ?? [])
            ->setLimit($options['limit'] ?? SiteOptions::get('items_per_page'))
            ->setOffset($options['offset'] ?? 0);

        $ObjectOptions = (new ObjectOptions())
            ->setWithExtraData($options['lazyLoad'] ?? false)
            ->setForOutput($options['forPrint'] ?? false);

        return self::baseFetch($Meta, $FetchBy, $FetchOptions, $ObjectOptions);
    }

    /**
     * Возвращает первый найденный объект.
     *
     * @see     EntityManager::baseFind()
     * @param   array  $restricts
     * @param   array  $options
     * @return  AbstractEntity|FALSE
     */
    public static function findFirst(array $restricts = [], array $options = [])
    {
        $options['count']  = false;
        $options['limit']  = 1;
        $options['offset'] = 0;

        return current(static::find($restricts, $options));
    }

    /**
     * Выборка из БД значений полей сущностей.
     *
     * @param   AbstractEntityMeta  $Meta           Метаинформация
     * @param  ?FetchBy             $FetchBy        Параметры поиска
     * @param  ?FetchOptions        $FetchOptions   Параметры выборки :
     * ~~~
     *   bool   $count      = false
     *   array  $select     = []
     *   mixed  $orderBy    = []
     *   int    $limit      = Cfg::DEFAULT_RECORDS_LIMIT
     *   int    $offset     = 0
     *   bool   $rawRecords = false
     * ~~~
     * @param  ?ObjectOptions       $ObjectOptions  Параметры создания объектов :
     * ~~~
     *   bool  $withExtraData  = false
     *   bool  $forOutput      = false
     *   bool  $showSensitive  = false
     * ~~~
     * @return  array
     */
    final public static function baseFetch(
        AbstractEntityMeta $Meta,
        FetchBy $FetchBy = null,
        FetchOptions $FetchOptions = null,
        ObjectOptions $ObjectOptions = null
    ): array {
        is_null($FetchBy)       && $FetchBy = new FetchBy();
        is_null($FetchOptions)  && $FetchOptions = new FetchOptions();
        is_null($ObjectOptions) && $ObjectOptions = new ObjectOptions();

        $query = [
            'calc_found_rows' => $FetchOptions->getCount(),
            'select'          => $FetchOptions->getSelect( $Meta::getDBTable() ),
            'from'            => DBCommand::qC( $Meta::getDBTable() ),
            'where'           => $FetchBy->buildWhereFromMeta($Meta),
            'orderBy'         => self::validateOrderBy($Meta, $FetchOptions->getOrderBy()),
            'limit'           => $FetchOptions->getLimit(),
            'offset'          => $FetchOptions->getOffset()
        ];

        $FetchOptions->assignQuery($query);

        $dbRows = DBCommand::select($query);

        if ($FetchOptions->getCount()) {
            DBCommand::calcFoundRows();
        }

        if ($FetchOptions->getRawRecords()) {
            return $dbRows;
        }

        $ObjectOptions->setSkipValidation();

        return static::toObjects($dbRows, $ObjectOptions);
    }

    /**
     * Преобразование результата [[static::fetch()]] в массив.
     *
     * @param  ?FetchBy        $FetchBy        Параметры поиска
     * @param  ?FetchOptions   $FetchOptions   Параметры выборки
     * @param  ?ObjectOptions  $ObjectOptions  Параметры создания объекта
     * @return  array
     */
    public static function fetchAsArray(
        FetchBy $FetchBy = null,
        FetchOptions $FetchOptions = null,
        ObjectOptions $ObjectOptions = null
    ): array {
        return objectToArray( static::fetch($FetchBy, $FetchOptions, $ObjectOptions) );
    }

    /**
     * Выборка из БД первой строки с полями сущности.
     *
     * @param  ?FetchBy        $FetchBy        Параметры поиска
     * @param  ?FetchOptions   $FetchOptions   Параметры выборки
     * @param  ?ObjectOptions  $ObjectOptions  Параметры создания объекта
     * @param  ?string         $className      Класс нового объекта, который будет
     * @return  AbstractEntity|array|NULL      NULL, если в БД ничего не нашлось, а $className не задан
     */
    public static function fetchOne(
        FetchBy $FetchBy = null,
        FetchOptions $FetchOptions = null,
        ObjectOptions $ObjectOptions = null,
        string $className = null
    ) {
        is_null($FetchOptions) && $FetchOptions = new FetchOptions();

        $FetchOptions
            ->setLimit(1)
            ->setOffset(0);

        $fetchResult = static::fetch($FetchBy, $FetchOptions, $ObjectOptions);

        if (empty($fetchResult)) {
            if ($FetchOptions->getRawRecords()) {
                return [];
            }

            if ($className) {
                return self::createObject($className, [], $ObjectOptions);
            }

            return null;
        }

        return current($fetchResult);
    }

    /**
     * Возвращает общее количество объектов, подошедших под параметры поиска
     * ранее выполненного [[self::baseFetch()]].
     * ВАЖНО: между вызовами [[self::baseFetch()]] и этого метода не должны
     * выполняться другие выборки с параметром 'SQL_CALC_FOUND_ROWS'.
     *
     * @return int
     */
    public static function count(): int
    {
        return DBCommand::getFoundRows();
    }

    /**
     * Получение дополнительного HTML-кода для формы добавление/изменения объекта.
     *
     * @param   AbstractEntity  $Object  Объект, для которого генерируется HTML-форма
     * @param  ?string          $action  Тип операции, для которой нужна форма
     * @return  string
     */
    public static function getHtmlForSingleObjForm(
        AbstractEntity $Object,
        string $action = null
    ): string {
        return '';
    }

    /**
     * Генерация на основе метаинформации SQL-выражения для создания таблицы БД.
     *
     * @param   AbstractEntityMeta  $Meta  Метаинформация
     * @return  string
     */
    final public static function createTable(AbstractEntityMeta $Meta): string
    {
        $fieldsMeta = $Meta->getFields();
        $metaPK = $Meta::PRIMARY_KEY;
        $columns = [];

        foreach ($fieldsMeta as $field => $fieldMeta) {
            $col = [];
            switch ($fieldMeta['type']) {
                case FieldInfo::FT_BOOLEAN:
                    $col['type'] = 'tinyint';
                    $col['length'] = 1;
                    $col['unsigned'] = true;
                    break;
                case FieldInfo::FT_DATE:
                    $col['type'] = 'date';
                    break;
                case FieldInfo::FT_DATETIME:
                    $col['type'] = 'timestamp';
                    break;
                case FieldInfo::FT_TIME:
                case FieldInfo::FT_TEXT:
                case FieldInfo::FT_PASS:
                case FieldInfo::FT_FILEPATH:
                case FieldInfo::FT_URL:
                    $col['type'] = 'varchar';
                    break;
                case FieldInfo::FT_MULTILINETEXT:
                case FieldInfo::FT_HTML:
                    $col['type'] = isset($fieldMeta['maxlength']) ? 'varchar' : 'text';
                    break;
                case FieldInfo::FT_INTEGER:
                case FieldInfo::FT_STATUSES:
                    $col['type'] = 'int';
                    if (empty($fieldMeta['maxlength'])) {
                        $col['length'] = 10;
                    }
                    break;
                case FieldInfo::FT_FLOAT:
                    $col['type'] = 'double';
                    break;
                default:
                    break;
            }
            if (isset($fieldMeta['unsigned'])) {
                $col['unsigned'] = true;
            }
            if (isset($fieldMeta['maxlength'])) {
                $col['length'] = $fieldMeta['maxlength'];
            }
            if ($fieldMeta['required']) {
                $col['not_null'] = true;
            } else {
                $col['default'] = null;
            }
            if (in_array($field, $metaPK, true)) {
                $col['is_pk'] = true;
                $col['not_null'] = true;
                if (
                    $field === $metaPK[0]
                    && !isset($metaPK[1])
                    && $fieldsMeta[ $metaPK[0] ]['type'] === FieldInfo::FT_INTEGER
                ) {
                    $col['auto_inc'] = true;
                    unset($col['default']);
                }
            }
            $columns[ $field ] = $col;
        }

        return DBQueryBuilder::createTable($Meta::getDBTable(), $columns);
    }
}
