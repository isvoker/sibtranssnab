<?php
/**
 * Параметры выборки записей из БД методом [[EntityManager::baseFetch()]].
 *
 * @author Dmitriy Lunin
 */
class FetchOptions
{
    /**
     * Надо ли посчитать общее кол-во подходящих строк.
     *
     * @bool
     */
    protected $count = false;

    /**
     * Список требуемых полей. Если не задано, выбираются все.
     *
     * [ string $colAlias => string $colName, ... ]
     *
     * @array
     */
    protected $select = [];

    /**
     * Список полей, выбираемых из дополнительных таблиц,
     * добавленных при помощи [[FetchOptions::queryAppendix]].
     *
     * [ string $table => array $columns, ... ]
     *
     * @array
     */
    protected $externalSelect = [];

    /**
     * Параметры сортировки вида ['fieldName' => 'ASC'|'DESC', ...] или строка.
     *
     * [ string $colName|int position => DBQueryBuilder::<<ASC|DESC>>, ...] или строка
     *
     * @array|@string
     */
    protected $orderBy = [];

    /**
     * Ограничение кол-ва выбираемых записей.
     * При '-1' выбираются ВСЕ ЗАПИСИ.
     *
     * @int
     */
    protected $limit = -1;

    /**
     * Начиная с какой записи в БД производить поиск.
     *
     * @int
     */
    protected $offset = 0;

    /**
     * Пропустить создание объектов и отдать найденные записи как есть.
     *
     * @bool
     */
    protected $rawRecords = false;

    /**
     * Добавочные параметры для [[DBCommand::select()]].
     *
     * @array
     */
    protected $queryAppendix = [];

    /**
     * @see     FetchOptions::count
     * @param   bool  $count
     * @return  FetchOptions
     */
    public function setCount(bool $count = true): FetchOptions
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @see     FetchOptions::count
     * @return  bool
     */
    public function getCount(): bool
    {
        return $this->count;
    }

    /**
     * @see     FetchOptions::select
     * @param   array  $select
     * @return  FetchOptions
     */
    public function setSelect(array $select): FetchOptions
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @see     FetchOptions::externalSelect
     * @param   string  $table    Название таблицы
     * @param   array   $columns  Список столбцов
     * @return  FetchOptions
     */
    public function setExternalSelect(string $table, array $columns): FetchOptions
    {
        $this->externalSelect[ $table ] = $columns;
        return $this;
    }

    /**
     * @see     FetchOptions::select, FetchOptions::externalSelect
     * @param  ?string  $table  Если задано, будет использовано как ключ массива
     * @return  array[]   [ [ ... ] ] | [ $table => [ ... ], ... ]
     */
    public function getSelect(string $table = null): array
    {
        $select = $this->externalSelect;

        if (is_null($table)) {
            $select[] = $this->select;
        } else {
            $select[ $table ] = $this->select;
        }

        return $select;
    }

    /**
     * @see     FetchOptions::orderBy
     * @param   array|string  $orderBy
     * @return  FetchOptions
     */
    public function setOrderBy($orderBy): FetchOptions
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * Установка случайного порядка сортировки.
     *
     * @see https://tpetry.me/20210507-how-to-optimize-order-by-random
     *
     * @see     FetchOptions::orderBy
     * @return  FetchOptions
     */
    public function setOrderRandom(): FetchOptions
    {
        $this->orderBy = DBQueryBuilder::RAND;
        return $this;
    }

    /**
     * @see     FetchOptions::orderBy
     * @return  array|string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @see     FetchOptions::limit
     * @param   int  $limit
     * @return  FetchOptions
     */
    public function setLimit(int $limit = Cfg::DEFAULT_RECORDS_LIMIT): FetchOptions
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @see     FetchOptions::limit
     * @return  int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @see     FetchOptions::offset
     * @param   int  $offset
     * @return  FetchOptions
     */
    public function setOffset(int $offset): FetchOptions
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @see     FetchOptions::offset
     * @return  int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @see     FetchOptions::offset
     * @param   int  $page
     * @return  FetchOptions
     */
    public function setPage(int $page): FetchOptions
    {
        $this->offset = ($page - 1) * $this->limit;
        return $this;
    }

    /**
     * @see     FetchOptions::rawRecords
     * @param   bool  $rawRecords
     * @return  FetchOptions
     */
    public function setRawRecords(bool $rawRecords = true): FetchOptions
    {
        $this->rawRecords = $rawRecords;
        return $this;
    }

    /**
     * @see     FetchOptions::rawRecords
     * @return  bool
     */
    public function getRawRecords(): bool
    {
        return $this->rawRecords;
    }

    /**
     * Добавление дополнительных параметров, которые
     * будут использованы методом [[EntityManager::baseFetch()]]
     * для выборки из БД значений полей сущностей.
     *
     * @param   array  $queryAppendix
     * @return  FetchOptions
     */
    public function setQueryAppendix(array $queryAppendix): FetchOptions
    {
        $this->queryAppendix[] = $queryAppendix;
        return $this;
    }

    /**
     * Дополнение с перезаписью (array_merge) параметров,
     * передающихся методу [[DBCommand::select()]].
     * Значения-массивы дополняются, значения-строки заменяются.
     *
     * @param  array  &$query
     */
    public function assignQuery(array &$query): void
    {
        foreach ($this->queryAppendix as $appendix) {
            foreach ($appendix as $arg => $value) {
                $query[ $arg ] = is_array($query[ $arg ] ?? null)
                    ? array_merge($query[ $arg ], is_array($value) ? $value : [ $value ])
                    : $value;
            }
        }
    }
}
