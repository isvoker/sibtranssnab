<?php
/**
 * Статичный класс DBCommand.
 *
 * DBCommand предоставляет интерфейс для выполнения SQL-запросов.
 * Для взаимодействия с СУБД используется расширение MySQLi.
 *
 * @author Dmitriy Lunin
 */
class DBCommand
{
	/**
	 * Символ, используемый для экранирования
	 * имён таблиц, столбцов и т.д.
	 */
	public const QUOTE_NAME_SYMBOL = '`';

	/** Обозначения ожидаемого формата результата выполнения SQL-запроса */

	// результат выполнения [[self::execute()]] без обработки
	public const OUTPUT_DEFAULT = null;
	// результат выполнения [[self::execute()]] без обработки
	public const OUTPUT_RAW = 'res';
	// ID, генерируемый запросом INSERT
	public const OUTPUT_INSERT_ID = 'ins';
	// массив из значений первого столбца в найденных строках результата выборки
	public const OUTPUT_FIRST_COLUMN = 'col';
	// первая строка результата выборки
	public const OUTPUT_FIRST_ROW = 'row';
	// значение первого столбца первой строки результата выборки
	public const OUTPUT_FIRST_CELL = 'cell';
	// ключи массива результатов равны значению 'id' соотв. строки
	public const OUTPUT_ID_AS_KEY = 'by id';

	/** Размер частей, на которые разбиваются массивы данных методом [[self::batchInsert()]] */
	protected const BATCH_INSERT_CHUNK_LENGTH = 500;

	/** Подключение в БД, объект mysqli */
	protected static $Connection;

	/** Кол-во строк, соответствующих ранее выполненному запросу (без учёта LIMIT) */
	protected static $foundRows = 0;

	/** Кэш ранее выполненных запросов */
	protected static $cache = [];

	/** Количество выполненных запросов */
	protected static $numQueries = 0;

	/** Запрет создания экземпляров через new */
	protected function __construct() {}

	/** Запрет создания экземпляров через клонирование */
	protected function __clone() {}

	/** Установка соединения с БД */
	public static function connect(): void
	{
		if (self::isConnected()) {
			return;
		}

		ob_start();
		self::$Connection = new mysqli(
			Cfg::DB_HOSTNAME,
			Cfg::DB_USERNAME,
			str_rot13(Cfg::DB_PASSWORD),
			Cfg::DB_BASENAME,
			Cfg::DB_PORT ?: ini_get('mysqli.default_port')
		);
		ob_get_clean();

		if (self::$Connection->connect_errno) {
			die('Could not connect to the database (connection error #'
				. self::$Connection->connect_errno . ': '
				. self::$Connection->connect_error . ')');
		}

		if (Cfg::DB_CHARSET) {
			self::$Connection->set_charset(Cfg::DB_CHARSET);
		}

		register_shutdown_function('DBCommand::close');
	}

	/** Закрытие текущего соединения с БД */
	public static function close(): void
	{
		if (self::isConnected()) {
			self::$Connection->close();
			self::$Connection = null;
		}
	}

	/**
	 * Проверка соединения с БД.
	 *
	 * @return bool
	 */
	public static function isConnected(): bool
	{
		return !empty(self::$Connection) && !self::$Connection->connect_errno;
	}

	/**
	 * Получение символа, используемого для заключения в кавычки имён таблиц, столбцов и т.д.
	 *
	 * @return string
	 */
	public static function getQuoteNameSymbol(): string
	{
		return self::QUOTE_NAME_SYMBOL;
	}

	/**
	 * Получение типа используемого соединения с сервером БД.
	 *
	 * @return string
	 */
	public static function getHostInfo(): string
	{
		return self::$Connection->host_info;
	}

	/**
	 * Получение версии сервера БД.
	 *
	 * @return string
	 */
	public static function getServerInfo(): string
	{
		return self::$Connection->server_info;
	}

	/**
	 * Получение названия схемы
	 *
	 * @return string
	 */
	public static function getSchema(): string
	{
		return Cfg::DB_BASENAME;
	}

	/**
	 * Получение списка таблиц базы данных.
	 *
	 * @return array
	 */
	public static function getTables(): array
	{
		return self::select([
			'select' => 'table_name',
			'from'   => 'information_schema.tables',
			'where'  => 'table_schema = \'' . self::getSchema() . '\'',
			'order'  => 'table_name'
		], self::OUTPUT_FIRST_COLUMN);
	}

	/**
	 * Проверка доступности таблицы.
	 *
	 * @param   string  $table  Название таблицы
	 * @return  bool
	 */
	public static function getTableIsExists(string $table): bool
	{
		return (bool) self::select([
			'select' => '1',
			'from'   => 'information_schema.tables',
			'where'  => 'table_schema = \'' . self::getSchema()
				. '\' AND table_name = ' . self::qV($table),
			'order'  => 'table_name'
		], self::OUTPUT_FIRST_CELL);
	}

	/**
	 * Запись запроса в лог.
	 * Пишутся все запросы кроме SELECT'ов в файл и/или БД в зависимости от того,
	 * что включено в файле конфигурации.
	 *
	 * @param  string  $query  Текст запроса
	 */
	protected static function logQuery(string $query): void
	{
		/* log query to file */
		//Logger::info('sql', $query, Logger::FILE_HANDLER);
		if (Cfg::SQL_LOG_TO_FILE && stripos($query, 'SELECT ') !== 0) {
			FsFile::addTo(Cfg::SQL_LOG_TO_FILE, '-- ' . Time::toSQLDateTime() . "\n{$query}\n");
		}

		/* log query to DB */
		if (
			Cfg::SQL_LOG_TO_DB
			&& Cfg::HISTORY_IS_ON
			&& stripos($query, 'SELECT ') !== 0
			&& strpos($query, '###SQL_LOG###') === false
		) {
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_clean();
			$trace = preg_replace('/^#.+DBCommand::query.+(#\d)/sU', '$1', $trace, 1);

			HistoryManager::addHistory(
				"###SQL_LOG### Query : [{$query}] ###\nBacktrace:\n" . $trace
			);
		}
	}

	/**
	 * Допустимо ли для заданного запроса использовать кэш?
	 *
	 * @param   string  $query  Текст запроса
	 * @return  bool
	 */
	protected static function useCacheInThisOnce(string $query): bool
	{
		return Cfg::SQL_CACHE_IS_ON && stripos($query, 'SELECT ') === 0;
	}

	/** Очистка кэша ранее выполненных запросов */
	public static function clearCache(): void
	{
		self::$cache = [];
	}

	/**
	 * Выполнение SQL-запроса.
	 *
	 * @param   string  $query  Текст запроса
	 * @return  mixed   see https://secure.php.net/manual/mysqli.query.php
	 */
	protected static function execute(string $query)
	{
		if (!self::isConnected()) {
			throw new DBCommandEx( DBCommandEx::CONNECTION );
		}
		self::logQuery($query);
		$result = self::$Connection->query($query);
		++self::$numQueries;

		if ($result === false) {
			if (Cfg::DEBUG_IS_ON) {
				throw new DBCommandEx(
					"Error in SQL [{$query}] #" . self::$Connection->errno . ': '
						. self::$Connection->error
				);
			}
			throw new DBCommandEx( DBCommandEx::SYNTAX );
		}

		return $result;
	}

	/**
	 * Получение количества выполненных запросов.
	 *
	 * @return int
	 */
	public static function getNumQueries(): int
	{
		return self::$numQueries;
	}

	/**
	 * Выполнение SQL-запроса.
	 * В зависимости от значения $expected возможно
	 * приведение результата к определённому формату.
	 *
	 * @param   string  $query     SQL-запрос
	 * @param   string  $expected  Указание на требуемый вид результата.
	 *                             Для запросов SELECT, SHOW, DESCRIBE или EXPLAIN
	 *                             по умолчанию возвращается массив ассоциативных [rows].
	 *                             Не NULL по умолчанию сверяется с шаблоном 'by\s(?P<column>.+)', и в случае сходства
	 *                             результат будет вида [[self::OUTPUT_ID_AS_KEY]], но для указанного столбца.
	 * @param   bool    $useCache  Если TRUE, без проверки других условий будет задействован кэш
	 * @return  mixed
	 */
	public static function query(
		string $query,
		string $expected = self::OUTPUT_DEFAULT,
		bool $useCache = false
	) {
		if (empty($query)) {
			throw new InvalidArgumentException('Error in SQL: query is empty');
		}

		if ($useCache || self::useCacheInThisOnce($query)) {
			$hash = hash('md5', $query . $expected);

			if (isset(self::$cache[ $hash ])) {
				return self::$cache[ $hash ];
			}

			$useCacheInThisOnce = true;
		} else {
			$useCacheInThisOnce = false;
		}

		$result = false;
		$raw = self::execute($query);
		switch ($expected) {
			case null:
				if ($raw === true) {
					return true;
				}

				$result = [];
				while (($row = $raw->fetch_assoc()) !== null) {
					$result[] = $row;
				}
				break;

			case self::OUTPUT_RAW:
				$result = $raw;
				break;

			case self::OUTPUT_INSERT_ID:
				$result = self::$Connection->insert_id;
				break;

			case self::OUTPUT_FIRST_COLUMN:
				$result = [];
				while ($row = $raw->fetch_row()) {
					$result[] = $row[0];
				}
				break;

			case self::OUTPUT_FIRST_ROW:
			case 'str':
				$result = $raw->fetch_assoc();
				if (!is_array($result)) {
					$result = false;
				}
				break;

			case self::OUTPUT_FIRST_CELL:
			case 'one':
				if (($row = $raw->fetch_row()) && array_key_exists(0, $row)) {
					$result = $row[0];
				}
				break;

			case self::OUTPUT_ID_AS_KEY:
				$result = [];
				while ($row = $raw->fetch_assoc()) {
					$result[ $row['id'] ] = $row;
				}
				break;

			default:
				if (preg_match('#by\s(.+)#mi', $expected, $matches)) {
					$field = $matches[1];
					$result = [];
					while ($row = $raw->fetch_assoc()) {
						$result[ $row[ $field ] ] = $row;
					}
				}
				break;
		}

		if ($raw !== true) {
			$raw->free();
		}

		if ($useCacheInThisOnce) {
			self::$cache[ $hash ] = $result;
		}

		return $result;
	}

	/**
	 * Построение и выполнение SQL-запроса SELECT.
	 *
	 * @see     self::query()
	 * @see     DBQueryBuilder::select()
	 * @param   array   $query
	 * @param  ?string  $expected
	 * @param   bool    $useCache
	 * @param   array   $modifiers
	 * @return  mixed
	 */
	public static function select(
		array $query,
		?string $expected = null,
		bool $useCache = false,
		array $modifiers = []
	) {
		if (
			($expected === self::OUTPUT_FIRST_ROW || $expected === self::OUTPUT_FIRST_CELL)
			&& !isset($query['limit'])
		) {
			$query['limit'] = 1;
		}

		return self::query(
			DBQueryBuilder::select($query, $modifiers),
			$expected,
			$useCache
		);
	}

	/**
	 * Построение и выполнение SQL-запроса INSERT.
	 *
	 * @see     DBQueryBuilder::insert()
	 * @param   string  $table
	 * @param   array   $values
	 * @param   bool    $sparse
	 * @param   array   $modifiers
	 * @return  int     ID, генерируемый запросом "INSERT"
	 */
	public static function insert(
		string $table,
		array $values = [],
		bool $sparse = false,
		array $modifiers = []
	): int {
		return self::query(
			DBQueryBuilder::insert($table, $values, $sparse, $modifiers),
			self::OUTPUT_INSERT_ID
		);
	}

	/**
	 * Построение и выполнение SQL-запросов INSERT
	 * для вставки большого количества (сотен, тысяч...) строк:
	 * массив $values разделяется на части по $chunkLength элементов,
	 * для каждой части формируется один SQL-запрос.
	 *
	 * @see    self::insert()
	 * @param  string  $table
	 * @param  array   $values
	 * @param  int     $chunkLength
	 */
	public static function batchInsert(
		string $table,
		array $values,
		int $chunkLength = self::BATCH_INSERT_CHUNK_LENGTH
	): void {
		$values = array_chunk($values, $chunkLength);
		foreach ($values as $valuesPart) {
			self::insert($table, $valuesPart, true);
		}
	}

	/**
	 * Построение и выполнение SQL-запроса UPDATE.
	 *
	 * @see     DBQueryBuilder::update()
	 * @param   string  $table
	 * @param   array   $values
	 * @param   mixed   $where
	 * @param   array   $modifiers
	 * @return  bool
	 */
	public static function update(
		string $table,
		array $values,
		$where = null,
		array $modifiers = []
	): bool {
		return self::query(
			DBQueryBuilder::update($table, $values, $where, $modifiers)
		);
	}

	/**
	 * Построение и выполнение SQL-запроса DELETE.
	 *
	 * @see     DBQueryBuilder::delete()
	 * @param   string  $table
	 * @param   mixed   $where
	 * @param   array   $modifiers
	 * @param  ?string  $expected  Параметр для метода [[self::query()]]
	 * @return  bool
	 */
	public static function delete(
		string $table,
		$where = null,
		array $modifiers = [],
		?string $expected = null
	): bool {
		return self::query(
			DBQueryBuilder::delete($table, $where, $modifiers),
			$expected
		);
	}

	/**
	 * Сохранение количества строк, соответствующих
	 * предыдущему выполненному запросу с параметром SQL_CALC_FOUND_ROWS.
	 */
	public static function calcFoundRows(): void
	{
		self::$foundRows = self::query('SELECT FOUND_ROWS()', self::OUTPUT_FIRST_CELL);
	}

	/**
	 * Получение сохранённого значения количества строк, соответствующих
	 * ранее выполненному запросу с параметром SQL_CALC_FOUND_ROWS.
	 *
	 * @return int
	 */
	public static function getFoundRows(): int
	{
		return self::$foundRows;
	}

	/**
	 * Удаление таблицы.
	 *
	 * @param   string  $table  Название таблицы
	 * @return  bool
	 */
	public static function dropTable(string $table): bool
	{
		if (empty($table)) {
			return false;
		}

		self::query('DROP TABLE IF EXISTS ' . self::qC($table));

		return true;
	}

	/**
	 * Очистка всех строк в таблице.
	 *
	 * @param   string  $table  Название таблицы
	 * @return  bool
	 */
	public static function truncateTable(string $table): bool
	{
		if (empty($table)) {
			return false;
		}

		self::query('TRUNCATE TABLE ' . self::qC($table));

		return true;
	}

	/** Начало транзакции */
	public static function begin(): void
	{
		self::execute('BEGIN');
	}

	/** Завершение транзакции и сохранение изменений */
	public static function commit(): void
	{
		self::execute('COMMIT');
	}

	/** Откат транзакции */
	public static function rollback(): void
	{
		self::execute('ROLLBACK');
	}

	/** Disable Foreign Key Checks or Constraints */
	public static function disableConstraints(): void
	{
		self::execute('SET foreign_key_checks = 0');
	}

	/** Enable Foreign Key Checks or Constraints */
	public static function enableConstraints(): void
	{
		self::execute('SET foreign_key_checks = 1');
	}

	/**
	 * Заключение в кавычки имён таблиц, столбцов и т.д.
	 *
	 * @param   string  $name  Исходный идентификатор
	 * @return  string
	 */
	protected static function quotingName(string $name): string
	{
		$name = trim($name);
		if ($name === '*') {
			return $name;
		}

		return self::QUOTE_NAME_SYMBOL . self::eV($name) . self::QUOTE_NAME_SYMBOL;
	}

	/**
	 * Подготовка строки для использования в запросах к БД в качестве названия таблицы/столбца:
	 * 1) экранирование специальных символов для предотвращения инъекции;
	 * 2) заключение в кавычки.
	 * Если строка содержит точку, в кавычки по отдельности будут заключены
	 * части до и после последней точки.
	 *
	 * @param   string  $string  Исходная строка
	 * @return  string
	 */
	public static function qC(string $string): string
	{
		if ($dotPos = strrpos($string, '.')) {
			$prefix = self::quotingName(substr($string, 0, $dotPos)) . '.';
			$string = substr($string, $dotPos + 1);
		} else {
			$prefix = '';
		}

		return $prefix . self::quotingName($string);
	}

	/**
	 * Подготовка строки для использования в запросах к БД в качестве параметра:
	 * 1) экранирование специальных символов для предотвращения инъекции;
	 * 2) заключение в одинарные кавычки.
	 *
	 * @param   string|NULL  $string
	 * @return  string
	 */
	public static function qV($string): string
	{
		return $string === null ? 'NULL' : '\'' . self::eV($string) . '\'';
	}

	/**
	 * Подготовка строки для использования в запросах к БД:
	 * экранирование специальных символов для предотвращения инъекции.
	 *
	 * @param   string  $string
	 * @return  string
	 */
	public static function eV(string $string): string
	{
		if (!self::isConnected()) {
			throw new DBCommandEx( DBCommandEx::CONNECTION );
		}

		return self::$Connection->real_escape_string($string);
	}

	/**
	 * @see DBCommand::select()
	 * @deprecated
	 */
	public static function doSelect(
		array $query,
		string $expected = null,
		bool $useCache = false,
		array $modifiers = []
	) {
		trigger_error(
			'The DBCommand::doSelect() method is no longer supported',
			E_USER_DEPRECATED
		);
		return self::select($query, $expected, $useCache, $modifiers);
	}

	/**
	 * @see DBCommand::insert()
	 * @deprecated
	 */
	public static function doInsert(
		string $table,
		array $values = [],
		bool $sparse = false,
		array $modifiers = []
	): int {
		trigger_error(
			'The DBCommand::doInsert() method is no longer supported',
			E_USER_DEPRECATED
		);
		return self::insert($table, $values, $sparse, $modifiers);
	}

	/**
	 * @see DBCommand::update()
	 * @deprecated
	 */
	public static function doUpdate(
		string $table,
		array $newValues,
		$where = null,
		array $modifiers = []
	): bool {
		trigger_error(
			'The DBCommand::doUpdate() method is no longer supported',
			E_USER_DEPRECATED
		);
		return self::update($table, $newValues, $where, $modifiers);
	}

	/**
	 * @see DBCommand::delete()
	 * @deprecated
	 */
	public static function doDelete(
		string $table,
		$where = null,
		array $modifiers = [],
		string $expected = null
	): bool {
		trigger_error(
			'The DBCommand::doDelete() method is no longer supported',
			E_USER_DEPRECATED
		);
		return self::delete($table, $where, $modifiers, $expected);
	}
}
