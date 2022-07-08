<?php
/**
 * Статичный класс Dictionary.
 *
 * Методы для работы со словарями (наборами пар "код-значение")
 *
 * @author Pavel Nuzhdin <pnzhdin@gmail.com>
 * @author Dmitriy Lunin
 */
class Dictionary
{
    /** Имя таблицы БД со списком каталогов */
    public const DB_TABLE_DICTIONARIES = Cfg::DB_TBL_PREFIX . 'dictionaries';

    /** Имя таблицы БД с содержимым каталогов */
    public const DB_TABLE_VALUES = Cfg::DB_TBL_PREFIX . 'dictionary_values';

    /** Разделитель кодов в множественном выборе */
    public const MULTI_SEPARATOR = '|';

    /** Значение поля 'text' по умолчанию */
    public const DEFAULT_TEXT = '<без названия>';

    /** Ранее найденные пары {'dicAlias' => ['value' => 'text']} (кэш) */
    protected static $KNOWN_CODES = [];

    /**
     * Получение id, alias и name всех словарей.
     *
     * @return array
     */
    public static function getDictionaries(): array
    {
        return DBCommand::select([
            'from'  => self::DB_TABLE_DICTIONARIES,
            'order' => 'id'
        ]);
    }

    /**
     * Получение информации о словаре по идентификатору.
     *
     * @param   int  $dicId  ID словаря
     * @return  array
     */
    public static function getDictionaryById(int $dicId): array
    {
        return DBCommand::select([
            'from'  => self::DB_TABLE_DICTIONARIES,
            'where' => 'id = ' . DBCommand::qV($dicId)
        ], DBCommand::OUTPUT_FIRST_ROW);
    }

    /**
     * Увеличение номера ревизии данных словаря.
     *
     * @param  int  $dicId  ID словаря
     */
    protected static function incrementRevision(int $dicId): void
    {
        DBCommand::update(
            self::DB_TABLE_DICTIONARIES,
            ['revision' => '= revision + 1'],
            'id = ' . DBCommand::qV($dicId)
        );
    }

    /**
     * Проверка наличия обновления данных словаря, хранящихся в кэше.
     *
     * @param   string     $dicAlias         Alias словаря
     * @param   mixed      $revisionInCache  Номер ревизии данных в кэше
     * @return  false|int  FALSE, если обновления нет, или номер актуальной ревизии
     */
    public static function checkRevisionUpdate(string $dicAlias, $revisionInCache)
    {
        $revision = (int) DBCommand::select([
            'select' => [['revision']],
            'from'   => self::DB_TABLE_DICTIONARIES,
            'where'  => 'alias = ' . DBCommand::qV($dicAlias)
        ], DBCommand::OUTPUT_FIRST_CELL);

        return !is_numeric($revisionInCache) || $revision > (int) $revisionInCache
            ? $revision
            : false;
    }

    /**
     * Возвращает значение из словаря по коду ИЛИ false, если код не был найден.
     *
     * @param   string  $dicAlias  Alias словаря
     * @param   int     $code      Код
     * @return  string|bool
     */
    public static function getText(string $dicAlias, int $code)
    {
        if (!isset(self::$KNOWN_CODES[ $dicAlias ])) {
            self::$KNOWN_CODES[ $dicAlias ] = [];
        }

        if (!isset(self::$KNOWN_CODES[ $dicAlias ][ $code ])) {
            self::$KNOWN_CODES[ $dicAlias ][ $code ] = DBCommand::select([
                'select' => [['text']],
                'from'   => self::DB_TABLE_VALUES,
                'where'  => ':deleted: = 0 AND :id: = ' . DBCommand::qV($code)
            ], DBCommand::OUTPUT_FIRST_CELL);
        }

        return self::$KNOWN_CODES[ $dicAlias ][ $code ];
    }

    /**
     * Возвращает строку из значений словаря (через разделитель) по строке из кодов
     * (множественный выбор с разделителем).
     *
     * @param   string  $dicAlias  Alias словаря
     * @param   string  $codes     Коды через разделитель
     * @param   string  $glue      Разделитель для итоговой строки
     * @return  string
     */
    public static function getTextMulti(string $dicAlias, string $codes, string $glue = ', '): string
    {
        $values = '';

        if ($codes[0] === self::MULTI_SEPARATOR) {
            $codes = mb_substr($codes, 1);
        }
        foreach (explode(self::MULTI_SEPARATOR, $codes) as $i => $code) {
            if ($i) {
                $values .= $glue;
            }
            $values .= self::getText($dicAlias, $code);
        }

        return $values;
    }

    /**
     * Возвращает код из словаря по значению ИЛИ false, если значение не было найдено.
     *
     * @param   string  $dicAlias  Alias словаря
     * @param   string  $text      Значение
     * @param  ?int     $parent    Код родительского элемента для поиска только его прямых потомков
     * @return  string|bool
     */
    public static function getCode(string $dicAlias, string $text, ?int $parent = null)
    {
        if (!isset(self::$KNOWN_CODES[ $dicAlias ])) {
            self::$KNOWN_CODES[ $dicAlias ] = [];
        }

        $code = false;
        if ($parent === null) {
            $code = array_search($text, self::$KNOWN_CODES[ $dicAlias ], true);
        }
        if ($code === false) {
            $dicTbl = self::DB_TABLE_DICTIONARIES;
            $valTbl = self::DB_TABLE_VALUES;

            $whereExpr = [
                'clause' => "{$dicTbl}.:alias: = {dicAlias}"
                    . " AND {$valTbl}.:deleted: = 0"
                    . " AND {$valTbl}.:text: ",
                'values' => [ $dicAlias ]
            ];
            if ($text === null) {
                $whereExpr['clause'] .= 'IS NULL';
            } else {
                $whereExpr['clause'] .= '= {text}';
                $whereExpr['values'][] = $text;
            }

            if ($parent) {
                $whereExpr['clause'] .= " AND {$valTbl}.:parent: = {parent}";
                $whereExpr['values'][] = $parent;
            }

            $code = DBCommand::select([
                'select' => [$valTbl => ['id']],
                'from'   => $valTbl,
                'join'   => "LEFT OUTER JOIN {$dicTbl} ON {$valTbl}.dictionary_id = {$dicTbl}.id",
                'where'  => [ $whereExpr ],
                'limit'  => 1
            ], DBCommand::OUTPUT_FIRST_CELL);
            self::$KNOWN_CODES[ $dicAlias ][ $code ] = $text;
        }

        return $code;
    }

    /**
     * Возвращает коды из словаря по значениям (множественный выбор с разделителем).
     *
     * @param   string  $dicAlias  Alias словаря
     * @param   array   $texts     Значения
     * @return  string
     */
    public static function getCodes(string $dicAlias, array $texts): string
    {
        $codes = '';
        foreach ($texts as $text) {
            $code = self::getCode($dicAlias, $text);
            if ($code) {
                $codes .= self::MULTI_SEPARATOR . $code;
            }
        }

        return $codes;
    }

    /**
     * Получение полной иерархии родителей записи в словаре.
     *
     * @param   string  $dicAlias   Alias словаря
     * @param   int     $code       Код записи
     * @param   array   $hierarchy  Записи, найденные на предыдущих итерациях рекурсивного выполнения
     * @return  array
     */
    public static function getParents(string $dicAlias, int $code, array $hierarchy = []): array
    {
        array_unshift($hierarchy, ['value' => $code, 'text' => self::getText($dicAlias, $code)]);

        $dicTbl = self::DB_TABLE_DICTIONARIES;
        $valTbl = self::DB_TABLE_VALUES;

        $parent = DBCommand::select([
            'select' => [ $valTbl => ['parent'] ],
            'from'   => $valTbl,
            'join'   => "LEFT OUTER JOIN {$dicTbl} ON {$valTbl}.dictionary_id = {$dicTbl}.id",
            'where'  => [
                'clause' => "{$dicTbl}.:alias: = {dicAlias} AND {$valTbl}.:id: = {code}",
                'values' => [ $dicAlias, $code ]
            ]
        ], DBCommand::OUTPUT_FIRST_CELL);

        if ($parent) {
            $hierarchy = self::getParents($dicAlias, $parent, $hierarchy);
        }

        return $hierarchy;
    }

    /**
     * Получение записей из словаря.
     * Необходимо указать либо ID словаря, либо его Ident.
     *
     * @param   int|string  $dictionary  ID или Alias словаря
     * @param  ?string      $parent      Код родительского элемента для поиска только его потомков или 'NULL'
     * @param  ?string      $textLike    LIKE-выражение для значения
     * @param  ?int         $limit       Максимальное количество возвращаемых записей
     * @return  array
     */
    public static function getRowsFromDic(
        $dictionary,
        ?string $parent = null,
        ?string $textLike = null,
        ?int $limit = null
    ): array {
        $dicId = $dicAlias = null;

        if (is_numeric($dictionary)) {
            $dicId = $dictionary;
        } elseif (is_string($dictionary)) {
            $dicAlias = $dictionary;
        } else {
            throw new InvalidArgumentException('Dictionary is not defined');
        }

        $dicTbl = self::DB_TABLE_DICTIONARIES;
        $valTbl = self::DB_TABLE_VALUES;

        $query = [
            'select' => [ $valTbl => ['value' => 'id', 'text'] ],
            'from'   => $valTbl,
            'where'  => [],
            'order'  => "{$valTbl}.posit",
            'limit'  => $limit
        ];

        if ($dicId) {
            $query['where'][] = [
                'oper' => 'AND',
                'clause' => "{$valTbl}.:dictionary_id: = {dicId}",
                'values' => [ $dicId ]
            ];
        } else {
            $query['join'] = "LEFT OUTER JOIN {$dicTbl} ON {$valTbl}.dictionary_id = {$dicTbl}.id";
            $query['where'][] = [
                'oper' => 'AND',
                'clause' => "{$dicTbl}.:alias: = {dicAlias}",
                'values' => [ $dicAlias ]
            ];
        }

        $query['where'][] = [
            'oper' => 'AND',
            'clause' => "{$valTbl}.:deleted: = 0"
        ];

        if ($parent !== null) {
            $parentClause = "{$valTbl}.:parent: ";
            if ($parent === 'NULL') {
                $parentClause .= 'IS NULL';
            } else {
                $parentClause .= '= {parent}';
            }
            $query['where'][] = [
                'oper' => 'AND',
                'clause' => $parentClause,
                'values' => [ $parent ]
            ];
        }
        if ($textLike !== null) {
            $query['where'][] = [
                'oper' => 'AND',
                'clause' => "{$valTbl}.:text: LIKE '{valueLike}'",
                'values' => [$textLike => true]
            ];
        }

        $rows = DBCommand::select($query);

        if ($rows && $dicAlias) {
            foreach ($rows as $row) {
                self::$KNOWN_CODES[ $dicAlias ][ $row['value'] ] = $row['text'];
            }
        }

        return $rows;
    }

    /**
     * Возвращает список дочерних элементов, пригодный для использования в EasyUI tree.
     *
     * @param   string  $dicAlias  Alias словаря
     * @param   string  $parent    Код родительского элемента для поиска только его потомков
     * @return  array
     */
    public static function getTreeNodes(string $dicAlias, string $parent): array
    {
        $rows = self::getRowsFromDic($dicAlias, $parent);

        $from = self::DB_TABLE_VALUES;

        foreach ($rows as &$row) {
            $query = [
                'select' => 'COUNT(1)',
                'from'   => $from,
                'where'  => [[
                    'clause' => ':parent: = {id} AND :deleted: = 0',
                    'values' => [ $row['id'] ]
                ]]
            ];

            $row['state'] = DBCommand::select($query, DBCommand::OUTPUT_FIRST_CELL) ? 'closed' : 'open';
        }

        return $rows;
    }

    /**
     * Возвращает список дочерних элементов, пригодный для использования в EasyUI tree.
     *
     * @param   int  $dicId   Код словаря
     * @param   int  $parent  Код родительского элемента для поиска только его потомков
     * @return  array
     */
    public static function getTreeNodesByDicId(int $dicId, int $parent): array
    {
        $valTbl = self::DB_TABLE_VALUES;

        return DBCommand::select([
            'select' => [
                $valTbl => ['id', 'parent', 'text'],
                'state' => ['state' => "IF(state.id IS NULL, 'open', 'closed')"]
            ],
            'from' => $valTbl,
            'join' => "LEFT JOIN {$valTbl} AS state ON {$valTbl}.id = state.parent",
            'where' => [[
                'clause' => ":{$valTbl}:.:dictionary_id: = {dicId} AND :{$valTbl}:.:deleted: = 0 "
                    . "AND :{$valTbl}:.:parent: " . ($parent ? '= {parent}' : 'IS NULL'),
                'values' => [ $dicId, $parent ]
            ]],
            'group' => "{$valTbl}.id",
            'order' => "{$valTbl}.posit, {$valTbl}.text"
        ]);
    }

    /**
     * Добавление с словарь новой записи.
     *
     * @param   int     $dicId   Код словаря
     * @param  ?int     $parent  Код родительского элемента
     * @param   string  $text    Значение
     * @return  int     Код добавленной записи
     */
    public static function createRow(
        int $dicId,
        ?int $parent = null,
        string $text = self::DEFAULT_TEXT
    ): int {
        if (!User::isInGroup([Cfg::GRP_ADMINS, Cfg::GRP_DEVELS], false)) {
            throw new AccessDeniedEx();
        }

        if (!$parent) {
            $parent = null;
        }
        $id = DBCommand::insert(
            self::DB_TABLE_VALUES,
            [
                'dictionary_id' => $dicId,
                'text' => $text,
                'parent' => $parent
            ]
        );

        self::incrementRevision($dicId);

        HistoryManager::addHistory("Добавление в справочник #{$dicId} элемента #{$id}");

        return $id;
    }

    /**
     * Изменение значения записи.
     *
     * @param  int     $dicId  Код словаря
     * @param  int     $id     Код записи
     * @param  string  $text   Новое значение
     */
    public static function updateRow(int $dicId, int $id, string $text): void
    {
        if (!User::isInGroup([Cfg::GRP_ADMINS, Cfg::GRP_DEVELS], false)) {
            throw new AccessDeniedEx();
        }

        DBCommand::update(
            self::DB_TABLE_VALUES,
            ['text' => $text],
            'id = ' . DBCommand::qV($id)
        );

        self::incrementRevision($dicId);

        HistoryManager::addHistory("Изменение в справочнике #{$dicId} значения элемента #{$id} на \"{$text}\"");
    }

    /**
     * Удаление записи.
     *
     * @param  int  $dicId  Код словаря
     * @param  int  $id     Код записи
     */
    public static function deleteRow(int $dicId, int $id): void
    {
        if (!User::isInGroup([Cfg::GRP_ADMINS, Cfg::GRP_DEVELS], false)) {
            throw new AccessDeniedEx();
        }

        DBCommand::update(
            self::DB_TABLE_VALUES,
            ['deleted' => '1'],
            'id = ' . DBCommand::qV($id)
        );

        self::incrementRevision($dicId);

        HistoryManager::addHistory("Удаление из справочника #{$dicId} элемента #{$id}");
    }

    /**
     * Изменение родителя записи.
     *
     * @param  int     $dicId      Код словаря
     * @param  int     $id         Код записи
     * @param  int     $targetId   Код родителя
     * @param  string  $operation  Вид операции: 'append'|'top'|'bottom'
     */
    public static function dndRow(int $dicId, int $id, int $targetId, string $operation): void
    {
        if (!User::isInGroup([Cfg::GRP_ADMINS, Cfg::GRP_DEVELS], false)) {
            throw new AccessDeniedEx();
        }

        if ($operation === 'append') {
            $parent = $targetId;
        } else { //if ($operation == 'top' || $operation == 'bottom') ...
            $parent = DBCommand::select([
                'select' => [['parent']],
                'from'   => self::DB_TABLE_VALUES,
                'where'  => [[
                    'clause' => ':id: = {targetId} AND :dictionary_id: = {dicId}',
                    'values' => [ $targetId, $dicId ]
                ]]
            ], DBCommand::OUTPUT_FIRST_CELL);
        }

        DBCommand::update(
            self::DB_TABLE_VALUES,
            ['parent' => $parent],
            'id = ' . DBCommand::qV($id)
        );

        self::incrementRevision($dicId);

        HistoryManager::addHistory("Изменение в справочнике #{$dicId} родителя элемента #{$id} на #{$parent}");
    }

    /**
     * @deprecated
     * @see Dictionary::DB_TABLE_DICTIONARIES
     *
     * @return string
     */
    public static function getDBTableDictionaries(): string
    {
        trigger_error(
            'The Dictionary::getDBTableDictionaries() method is no longer supported',
            E_USER_DEPRECATED
        );
        return self::DB_TABLE_DICTIONARIES;
    }

    /**
     * @deprecated
     * @see Dictionary::DB_TABLE_VALUES
     *
     * @return string
     */
    public static function getDBTableValues(): string
    {
        trigger_error(
            'The Dictionary::getDBTableValues() method is no longer supported',
            E_USER_DEPRECATED
        );
        return self::DB_TABLE_VALUES;
    }
}
