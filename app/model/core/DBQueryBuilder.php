<?php
/**
 * Статичный класс DBQueryBuilder.
 *
 * Набор методов для построения SQL-выражений.
 *
 * @author Dmitriy Lunin
 */
class DBQueryBuilder
{
	/** Допустимые модификаторы запросов */
	public const ALLOWABLE_MODIFIERS = [
		'distinct'            => true, // select
		'low_priority'        => true, // insert, update, delete
		'delayed'             => true, // insert
		'high_priority'       => true, // select, insert
		'straight_join'       => true, // select
		'sql_small_result'    => true, // select
		'sql_big_result'      => true, // select
		'sql_buffer_result'   => true, // select
		'sql_cache'           => true, // select
		'sql_no_cache'        => true, // select
		'sql_calc_found_rows' => true, // select
		'ignore'              => true, // insert, update, delete
		'quick'               => true  // delete
	];

	public const MAX_BIGINT_UNSIGNED = '18446744073709551615'; // 2^64-1

	public const ASC = 'ASC';
	public const DESC = 'DESC';
	public const RAND = 'RAND';

	protected const CLAUSES_SEPARATOR = "\n";

	/**
	 * Преобразование списка модификаторов в строку.
	 *
	 * @param   array  $modifiers  Список модификаторов
	 * @return  string
	 */
	protected static function addModifiers(array $modifiers): string
	{
		// возможно, ещё стоит проверять $statement == 'select'|'insert'|...
		if (empty($modifiers)) {
			return '';
		}

		$string = '';
		foreach ($modifiers as $modifier => $flag) {
			if (isset(self::ALLOWABLE_MODIFIERS[ $modifier ])) {
				$string .= strtoupper($modifier) . ' ';
			}
		}

		return $string;
	}

	/**
	 * "Интерпретация" строки как части SQL-выражения:
	 *   - замена ":" на [[DBCommand::$quoteNameSymbol]];
	 *   - последовательные замены вхождений '/\{[\w\d_]+\}/' на соответствующие по номеру
	 * значения из $values, при этом к ключам применяется [[self::eV()]],
	 * а к значениям без заданного ключа - [[self::qV()]].
	 *
	 * @param   string  $string  Исходная строка
	 * @param   array   $values
	 * @return  string
	 */
	public static function interpret(string $string, array $values = []): string
	{
		$string = str_replace(':', DBCommand::getQuoteNameSymbol(), $string);

		if ($values) {
			$patterns = $replacement = [];
			foreach ($values as $value => $isEscapeOnly) {
				$patterns[] = '/\{[\w\d_]+\}/';
				$replacement[] = is_bool($isEscapeOnly)
					? DBCommand::eV($value)
					: DBCommand::qV($isEscapeOnly);
			}
			$string = preg_replace($patterns, $replacement, $string, 1);
		}

		return $string;
	}

	/**
	 * Форматирование даты в "ru"-стандарт.
	 *
	 * @param   string  $date
	 * @return  string
	 */
	public static function dateForHuman(string $date): string
	{
		return "DATE_FORMAT({$date}, \"%d.%m.%Y\")";
	}

	/**
	 * Форматирование даты и времени в "ru"-стандарт.
	 *
	 * @param   string  $datetime
	 * @return  string
	 */
	public static function datetimeForHuman(string $datetime): string
	{
		return "DATE_FORMAT({$datetime}, \"%d.%m.%Y %H:%i:%s\")";
	}

	/**
	 * Построение условного выражения - функции IF().
	 *
	 * @param   string  $when
	 * @param   string  $then
	 * @param   string  $else
	 * @return  string
	 */
	public static function conditional(string $when, string $then, string $else): string
	{
		return "IF({$when}, {$then}, {$else})";
	}

	/**
	 * Получение SQL-функции, возвращающей случайное значение
	 *
	 * @return string
	 */
	public static function getRandomFunction(): string
	{
		return 'RAND()';
	}

	/**
	 * Подготовка списка столбцов для выражения SELECT SQL-запроса.
	 * Если список столбцов представлен в виде строки, он возвращается без изменений,
	 * если в виде массива - применяется экранирование.
	 *
	 * Массив ожидается вида
	 * ['tblAlias1' => ['colAlias' => 'colName', ... ], 'tblAlias12' => ['*'], ... ]
	 * Алиасы не обязательны.
	 * Вместо названия столбца допустимо использовать выражения.
	 *
	 * @param   array|string  $select
	 * @return  string
	 */
	public static function prepareColumns($select): string
	{
		if (is_string($select)) {
			return $select;
		}

		if (!is_array($select)) {
			return '';
		}

		$comma = false;
		$sql = '';

		foreach ($select as $tblAlias => $columns) {
			if (!is_array($columns)) {
                throw new InvalidArgumentException('Error in SQL: `$columns` list must be an array');
			}

			$tblAlias = is_string($tblAlias) ? DBCommand::qC($tblAlias) . '.' : '';

			if (empty($columns)) {
				$columns = ['*'];
			}

			foreach ($columns as $colAlias => $column) {
				if (!is_string($column)) {
					throw new InvalidArgumentException('Error in SQL: column name must be a string');
				}

				if ($comma) {
					$sql .= ', ';
				} else {
					$comma = true;
				}

				if (strpos($column, '(') === false) {
					if ($column !== '*') {
						$column = DBCommand::qC($column);
					}
					$sql .= $tblAlias;
				}
				$sql .= $column;

				if (is_string($colAlias)) {
					$sql .= ' AS ' . DBCommand::qC($colAlias);
				}
			}
		}

		return $sql;
	}

	/**
	 * Построение выражения FROM для SQL-запроса.
	 * Если список таблиц представлен в виде строки, он подставляется без изменений.
	 * Таблицы и их алиасы, переданные массивом, подвергаются экранированию.
	 * Массив ожидается вида ['tblAlias' => 'tblName', ... ].
	 * Алиасы не обязательны.
	 *
	 * @param   array|string  $tables  Список таблиц
	 * @return  string
	 */
	public static function from($tables): string
	{
		if (empty($tables)) {
			return '';
		}

		if (is_string($tables)) {
			$sql = $tables;
		} elseif (is_array($tables)) {
			$sql = '';
			$comma = false;

			foreach ($tables as $tblAlias => $table) {
				if (!is_string($table)) {
					throw new InvalidArgumentException('Error in SQL: table name must be a string');
				}

				if ($comma) {
					$sql .= ', ';
				} else {
					$comma = true;
				}

				$sql .= DBCommand::qC($table);
				if (is_string($tblAlias)) {
					$sql .= ' AS ' . DBCommand::qC($tblAlias);
				}
			}
		} else {
			return '';
		}

		return 'FROM ' . $sql;
	}

	/**
	 * Построение выражения JOIN для SQL-запроса.
	 *
	 * @param   array|string  $joins  Join'ы массивом или одной строкой
	 * @return  string
	 */
	public static function join($joins): string
	{
		if (empty($joins)) {
			return '';
		}

		if (is_string($joins)) {
			return $joins;
		}

		if (is_array($joins)) {
			return implode(self::CLAUSES_SEPARATOR, array_filter($joins, 'is_string'));
		}

		return '';
	}

	/**
	 * Построение строки условий для выражения WHERE для SQL-запроса на основе списка предикатов.
	 *
	 * Варианты представления критериев поиска:
	 * 2) [['oper' => 'AND', // не обязательно; по умолчанию - 'OR'
	 *      'clause' => 'ГОТОВОЕ_ВЫРАЖЕНИЕ', // передаётся в [[self::interpret()]]
	 *      'values' => [123, 'foo' => true] // не обязательно; передаётся в [[self::interpret()]]
	 *     ], ...]
	 * 3) [['oper' => 'AND', // аналогично #2
	 *      'column' => 'name'|'tAlias.name',
	 *      'cond' => '>',   // не обязательно; по умолчанию - '='
	 *      'expr' => '123'  // SQL-безопасная строка со значением; передаётся в [[self::interpret()]]
	 *      'values' => [123, 'foo' => true] // не обязательно; передаётся в [[self::interpret()]]
	 *     ], ...]
	 *
	 * @param   array  $where  Критерии поиска
	 * @return  string
	 */
	protected static function wherePredicate(array $where): string
	{
		$predicate = '';
		foreach ($where as $key => $expr) {
			if ($key) {
				$predicate .= (isset($expr['oper']) ? " {$expr['oper']} " : ' OR ');
			}

			if (isset($expr['group'])) {

				$predicate .= '(' . self::wherePredicate($expr['group']) . ')';

			} elseif (isset($expr['clause'])) { // #2

				$predicate .= self::interpret($expr['clause'], $expr['values'] ?? []);

			} elseif (isset($expr['column'], $expr['expr'])) { // #3

				$expr['column'] = DBCommand::qC($expr['column']);
				if (!isset($expr['cond'])) {
					$expr['cond'] = '=';
				}
				$predicate .= "{$expr['column']} {$expr['cond']} " . self::interpret($expr['expr'], $expr['values'] ?? []);

			} else {
                throw new InvalidArgumentException('Error in SQL: invalid `$where` expression');
			}
		}

		return $predicate;
	}

	/**
	 * Построение выражения WHERE для SQL-запроса на основе предиката/списка предикатов.
	 * Варианты представления критериев поиска:
	 * 1) 'строка-готовое-выражение'
	 * 2-3) @see self::buildWherePredicate()
	 *
	 * @param   array|string  $where  Критерии поиска
	 * @return  string
	 */
	public static function where($where): string
	{
		if (empty($where)) {
			return '';
		}

		if (is_string($where)) { // #1
			$sql = self::interpret($where);
		} elseif (is_array($where)) {
			$sql = self::wherePredicate($where);
		} else {
			return '';
		}

		return $sql ? 'WHERE ' . $sql : '';
	}

	/**
	 * Построение выражения GROUP BY для SQL-запроса.
	 *
	 * @param   string  $columns  Список столбцов
	 * @return  string
	 */
	public static function groupBy(string $columns): string
	{
		return empty($columns) ? '' : 'GROUP BY ' . $columns;
	}

	/**
	 * Построение выражения HAVING для SQL-запроса.
	 *
	 * @param   string  $condition  Условия для результатов агрегатных функций
	 * @return  string
	 */
	public static function having(string $condition): string
	{
		return empty($condition) ? '' : 'HAVING ' . $condition;
	}

	/**
	 * Построение выражения UNION для SQL-запроса.
	 *
	 * @param   array|string  $unions  Список подзапросов в виде строки или массива
	 * @return  string
	 */
	public static function union($unions): string
	{
		if (empty($unions)) {
			return '';
		}

		if (is_string($unions)) {
			$sql = $unions;
		} elseif (is_array($unions)) {
			$sql = implode(
				self::CLAUSES_SEPARATOR . ') UNION (' . self::CLAUSES_SEPARATOR,
				array_filter($unions, 'is_string')
			);
		} else {
			return '';
		}

		return 'UNION (' . self::CLAUSES_SEPARATOR . $sql . self::CLAUSES_SEPARATOR . ')';
	}

	/**
	 * Построение выражения ORDER BY для SQL-запроса.
	 *
	 * @param   array|string  $orderBy  Параметры сортировки вида
	 *                        ['colName'|position => 'ASC'|'DESC', ...] или строка
	 * @return  string
	 */
	public static function orderBy($orderBy): string
	{
		if (empty($orderBy)) {
			return '';
		}

		if (is_string($orderBy)) {
			if ($orderBy === self::RAND) {
				$sql = self::getRandomFunction();
			} else {
				$sql = $orderBy;
			}
		} elseif (is_array($orderBy)) {
			$sql = '';
			$comma = false;
			foreach ($orderBy as $column => $direction) {
				if ($comma) {
					$sql .= ', ';
				} else {
					$comma = true;
				}
				if (is_numeric($column) && $column > 0) {
					$sql .= (int) $column;
				} else {
					$sql .= DBCommand::qC($column);
				}
				if (
					is_string($direction)
					&& strtoupper($direction) === self::DESC
				) {
					$sql .= ' DESC';
				}
			}
		} else {
			return '';
		}

		return 'ORDER BY ' . $sql;
	}

	/**
	 * Построение выражения LIMIT xx OFFSET yy для SQL-запроса.
	 *
	 * @param   int  $limit   Макс. кол-во возвращаемых строк ("-1" - без ограничений)
	 * @param   int  $offset  Позиция первой возвращаемой записи
	 * @return  string
	 */
	public static function limits($limit, $offset): string
	{
		$sql = '';

		if ($limit === -1) {
			$limit = self::MAX_BIGINT_UNSIGNED;
		} elseif (!is_numeric($limit) || !ctype_digit((string) $limit)) {
			$limit = 0;
		}

		if (
			!is_numeric($offset)
			|| (($offset = (string) $offset) && (!ctype_digit($offset) || $offset === '0'))
		) {
			$offset = 0;
		}

		if ($limit) {
			$sql = 'LIMIT ' . $limit;
			if ($offset) {
				$sql .= ' OFFSET ' . $offset;
			}
		} elseif ($offset) {
			$limit = self::MAX_BIGINT_UNSIGNED;
			$sql = "LIMIT {$limit} OFFSET {$offset}";
		}

		return $sql;
	}

	/**
	 * Построение SQL-запроса SELECT на основе заданных критериев.
	 * Поддерживаются следующие параметры (опции):
	 * DISTINCT, SQL_CALC_FOUND_ROWS.
	 * Поддерживаются следующие выражения:
	 * SELECT, FROM, JOIN, WHERE, GROUP, HAVING, UNION, ORDER, LIMIT, OFFSET.
	 *
	 * @param   array  $query      Параметры запроса в виде пар 'название'-'значение'
	 * @param   array  $modifiers  Список модификаторов
	 * @return  string
	 */
	public static function select(array $query, array $modifiers = []): string
	{
		$sql = [];

		if (!empty($query['distinct'])) {
			$modifiers['distinct'] = true;
		}
		if (!empty($query['calc_found_rows'])) {
			$modifiers['sql_calc_found_rows'] = true;
		}
		$sql[] = 'SELECT ' . self::addModifiers($modifiers)
			. (empty($query['select']) ? '*' : self::prepareColumns($query['select']));

		if (isset($query['from'])) {
			$sql[] = self::from($query['from']);
		}

		if (isset($query['join'])) {
			$sql[] = self::join($query['join']);
		}

		if (isset($query['where'])) {
			$sql[] = self::where($query['where']);
		}

		if (isset($query['group'])) {
			$sql[] = self::groupBy($query['group']);
		}

		if (isset($query['having'])) {
			$sql[] = self::having($query['having']);
		}

		if (isset($query['union'])) {
			$sql[] = self::union($query['union']);
		}

		if (isset($query['orderBy'])) {
			$sql[] = self::orderBy($query['orderBy']);
		} elseif (isset($query['order'])) {
			$sql[] = self::orderBy($query['order']);
		}

		if (isset($query['limit']) || isset($query['offset'])) {
			$sql[] = self::limits($query['limit'] ?? null, $query['offset'] ?? null);
		}

		return implode(self::CLAUSES_SEPARATOR, array_filter($sql));
	}

	/**
	 * Построение SQL-запроса INSERT.
	 * Возможна пакетная вставка сразу нескольких строк, в этом случае
	 * количество значений для каждого столбца должно совпадать!
	 *
	 * Допустимые форматы добавляемых данных:
	 * 1) ['col1' => 'val1', ...]
	 * 2) ['col1' => ['val11', ...], 'col2' => ['val21', ...], ...]
	 * 3) [['col1' => 'val11', 'col2' => 'val21', ...], ['col1' => 'val12', ...], ...]
	 *
	 * @param   string  $table      Название таблицы
	 * @param   array   $values     Добавляемые данные
	 * @param   bool    $sparse     Данные представлены в третьем варианте
	 * @param   array   $modifiers  Список модификаторов
	 * @return  string
	 */
	public static function insert(
		string $table,
		array $values = [],
		bool $sparse = false,
		array $modifiers = []
	): string {
		if (empty($table)) {
			throw new InvalidArgumentException('Error in SQL: table name is empty');
		}

		$sql = 'INSERT ' . self::addModifiers($modifiers) . 'INTO ' . DBCommand::qC($table);
		if (empty($values)) {
			return $sql;
		}

		$firstElem = current($values);
		$colNames = $sparse ? array_keys($firstElem) : array_keys($values);
		$sql .= ' (' . arrayToStr($colNames, ', ', 'DBCommand::qC') . ') VALUES';
		if (is_array($firstElem)) { // batch Insert
			$rows = [];
			if ($sparse) { // #3
				foreach ($values as $row) {
					$rows[] = '(' . arrayToStr($row, ', ', 'DBCommand::qV') . ')';
				}
			} else { // #2
				foreach ($firstElem as $i => $val) {
					$row = [];
					foreach ($colNames as $j => $col) {
						$row[ $j ] = $values[ $col ][ $i ];
					}
					$rows[] = '(' . arrayToStr($row, ', ', 'DBCommand::qV') . ')';
				}
			}
			$sql .= self::CLAUSES_SEPARATOR . implode(',' . self::CLAUSES_SEPARATOR, $rows);
		} else { // #1
			$sql .= ' (' . arrayToStr($values, ', ', 'DBCommand::qV') . ')';
		}

		return $sql;
	}

	/**
	 * Построение SQL-запроса UPDATE.
	 * Для использования столбца или выражения в качестве нового значения
	 * элемент 'значение' должен начинаться с символа '=' и быть SQL-безопасным.
	 *
	 * @see     self::buildWhereClause()
	 * @param   string  $table      Название таблицы
	 * @param   array   $values     Ассоц. массив ['col1' => 'value', 'col2' => '= col2 + 1', ...]
	 * @param   mixed   $where      Критерии поиска
	 * @param   array   $modifiers  Список модификаторов
	 * @return  string
	 */
	public static function update(
		string $table,
		array $values,
		$where = null,
		array $modifiers = []
	): string {
		if (empty($table) || empty($values)) {
			throw new InvalidArgumentException('Error in SQL: table name or assignment is empty');
		}

		$assignment = '';
		$comma = false;
		foreach ($values as $colName => $value) {
			if ($comma) {
				$assignment .= ', ';
			} else {
				$comma = true;
			}

			$assignment .= DBCommand::qC($colName);
			if (strpos($value, '=') === 0) {
				$assignment .= ' ' . $value;
			} else {
				$assignment .= ' = ' . DBCommand::qV($value);
			}
		}

		return 'UPDATE ' . self::addModifiers($modifiers) . DBCommand::qC($table)
			. " SET {$assignment} " . self::where($where);
	}

	/**
	 * Построение SQL-запроса DELETE.
	 *
	 * @see     self::buildWhereClause()
	 * @param   string  $table      Название таблицы
	 * @param   mixed   $where      Критерии поиска
	 * @param   array   $modifiers  Список модификаторов
	 * @return  string
	 */
	public static function delete(
		string $table,
		$where = null,
		array $modifiers = []
	): string {
		if (empty($table)) {
			throw new InvalidArgumentException('Error in SQL: table name is empty');
		}

		return 'DELETE ' . self::addModifiers($modifiers) . 'FROM ' . DBCommand::qC($table)
			. ' ' . self::where($where);
	}

	/**
	 * Построение SQL-запроса для создания таблицы. ТОЛЬКО ПРОТОТИП.
	 *
	 * @param   string  $table    Название таблицы
	 * @param   array   $columns  Список столбцов с их описаниями
	 * @return  string
	 */
	public static function createTable(string $table, array $columns): string
	{
		$query = 'CREATE TABLE IF NOT EXISTS ' . DBCommand::qC($table) . ' (';
		$pKeys = [];
		$comma = false;

		foreach ($columns as $col => $props) {
			if ($comma) {
				$query .= ',';
			} else {
				$comma = true;
			}

			$query .= PHP_EOL . '  '. DBCommand::qC($col) . ' ' . $props['type'];

			if (isset($props['length'])) {
				$query .= "({$props['length']})";
			}
			if (isset($props['unsigned'])) {
				$query .= ' UNSIGNED';
			}
			if (isset($props['not_null'])) {
				$query .= ' NOT NULL';
			}
			if (array_key_exists('default', $props)) {
				$query .= ' DEFAULT ' . ($props['default'] === null ? 'NULL' : "'{$props['default']}'");
			}
			if (isset($props['is_pk'])) {
				$pKeys[] = $col;
				if (!empty($props['auto_inc'])) {
					$query .= ' AUTO_INCREMENT';
				}
			}
		}

		if (!empty($pKeys)) {
			$query .= ',' . PHP_EOL . '  PRIMARY KEY (' . arrayToStr($pKeys, ',', 'DBCommand::qC') . ')';
		}

		$query .= PHP_EOL . ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

		return $query;
	}
}
