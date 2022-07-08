<?php

namespace Logger;

use Psr\Log\LogLevel;
use Throwable;

use Request;

/**
 * Поддержка логирования в консоль браузера по протоколу ChromePhp.
 *
 * Доступно для браузеров, представившихся как Chrome, Firefox 43+ или Opera 20+,
 * используется протокол Chrome Logger (в Firefox 43-56 имеется встроенная поддержка,
 * остальным надо установить дополнение "Chrome Logger").
 *
 * @author Dmitriy Lunin
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class LogToChromePHP extends AbstractLoggerHandler
{
	/** Имя обработчика */
	public const HANDLER = 'ChromePHP';

	/** Версия протокола */
	private static $VERSION = '4.0';

	/** Имя заголовка, в котором будут передаваться данные */
	private static $HEADER_NAME = 'X-ChromeLogger-Data';

	/** Флаг инициализации обработчика */
	private static $isInitialized = false;

	/**
	 * Ограничение размера отсылаемых заголовков.
	 *
	 * @see https://stackoverflow.com/questions/3326210/
	 */
	private static $HEADERS_SIZE_LIMIT = 240 * 1024;

	/** Флаг переполнения содержимого заголовка */
	private static $isOverflowed = false;

	/** Перечень обработанных объектов для предотвращения рекурсии */
	private static $processedObjects = [];

	/** Отправляемые данные в "сыром" виде */
	private static $json = [
		'columns' => ['label', 'log', 'backtrace', 'type'],
		'rows' => []
	];

	/**
	 * @see LogToInterface::isCompatible()
	 *
	 * Проверка на основе значения HTTP-заголовка "User-Agent".
	 *
	 * @return bool
	 */
	public static function isCompatible(): bool
	{
		return ($userBrowser = Request::getUserBrowser())
			&& (
				$userBrowser['browser'] === 'Chrome'
				|| ($userBrowser['browser'] === 'Firefox' && version_compare($userBrowser['version'], '43.0', '>='))
				|| ($userBrowser['browser'] === 'Opera' && version_compare($userBrowser['version'], '20.0', '>='))
			);
	}

	/**
	 * Активация обработчика.
	 */
	public static function init(): void
	{
		if (!self::$isInitialized) {
			self::$json['request_uri'] = Request::getUri();
			self::$json['version'] = self::$VERSION;

			self::$isInitialized = true;
		}
	}

	/**
	 * Одностороннее кодирование объекта короткой строкой.
	 *
	 * @param   object  $object
	 * @return  string
	 */
	protected static function objectToTag($object): string
	{
		return md5(json_encode($object, JSON_THROW_ON_ERROR));
	}

	/**
	 * Получение типа свойства объекта.
	 *
	 * @param   \ReflectionProperty  $Property
	 * @return  string
	 */
	protected static function getPropertyType(\ReflectionProperty $Property): string
	{
		$propertyName = ($Property->isStatic() ? ' static ' : '') . $Property->getName();

		if ($Property->isPublic()) {
			return 'public' . $propertyName;
		}
		if ($Property->isProtected()) {
			return 'protected' . $propertyName;
		}
		if ($Property->isPrivate()) {
			return 'private' . $propertyName;
		}
		return $propertyName;
	}

	/**
	 * Преобразование объекта для более наглядного отображения.
	 *
	 * @param   mixed  $object
	 * @return  mixed
	 */
	protected static function convert($object)
	{
		// If this isn't an object then just return it
		if (!is_object($object)) {
			return $object;
		}

		// Mark this object as processed so we don't convert it twice and it
		// Also avoid recursion when objects refer to each other
		self::$processedObjects[] = self::objectToTag($object);

		$objectAsArray = [
			'___class_name' => getClass($object)
		];

		// loop through object vars
		$objectVars = get_object_vars($object);
		foreach ($objectVars as $key => $value) {
			$objectAsArray[ $key ] =
				( $value === $object || isset(self::$processedObjects[ self::objectToTag($value) ]) )
					? 'recursion - parent object [' . getClass($value) . ']' // same instance as parent object
					: self::convert($value);
		}

		$Reflection = new \ReflectionClass($object);

		// loop through the properties and add those
		foreach ($Reflection->getProperties() as $Property) {
			// if one of these properties was already added above then ignore it
			if (array_key_exists($Property->getName(), $objectVars)) {
				continue;
			}

			$Property->setAccessible(true);
			$value = $Property->getValue($object);

			$objectAsArray[ self::getPropertyType($Property) ] =
				( $value === $object || isset(self::$processedObjects[ self::objectToTag($value) ]) )
					? 'recursion - parent object [' . getClass($value) . ']' // same instance as parent object
					: self::convert($value);
		}

		return $objectAsArray;
	}

	/**
	 * Промежуточное форматирование данных.
	 *
	 * @param   array  $data
	 * @return  array
	 */
	protected static function format(array $data): array
	{
		$backtrace = isset($data['extra']['file'], $data['extra']['line'])
			? $data['extra']['file'] . ' : ' . $data['extra']['line']
			: 'unknown';

		return [
			$data['label'] ?? 'Sensei Logger', // label
			$data['message'], // log
			$backtrace, // backtrace
			$data['level'] // type
		];
	}

	/**
	 * Кодирование данных для отправки в заголовке ответа.
	 *
	 * @param   array  $data
	 * @return  string
	 */
	protected static function encode(array $data): string
	{
		$json = json_encode(
			$data,
			JSON_UNESCAPED_SLASHES
			| JSON_PRESERVE_ZERO_FRACTION
			| JSON_INVALID_UTF8_SUBSTITUTE
			| JSON_THROW_ON_ERROR
		);

		if ($json === false) {
			$json = 'null';
		}

		return base64_encode(utf8_encode($json));
	}

	/**
	 * Получение информации о том, в каком месте был вызван
	 * метод класса [[Logger]], инициировавший сообщение.
	 *
	 * @return  array  ['file' => '...', 'line' => '...']
	 */
	protected static function getRowExtra(): array
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		array_shift($trace); // remove call to this method
		array_pop($trace); // remove {main}
		$trace = array_reverse($trace);

		foreach ($trace as $step) {
			if (
				isset($step['file'], $step['class'])
				&& $step['class'] === 'Logger'
			) {
				return [
					'file' => $step['file'],
					'line' => ($step['line'] ?? 0)
						. " [ {$step['class']}{$step['type']}{$step['function']}() ]"
				];
			}
		}

		return [];
	}

	/**
	 * Запись сообщения.
	 *
	 * @param  string  $level    Тип сообщения
	 * @param  string  $label    Заголовок сообщения
	 * @param  mixed   $message  Тело сообщения
	 */
	public static function write(string $level, string $label, $message): void
	{
		if (in_array(\PHP_SAPI, ['cli', 'cli-server', 'phpdbg'])) { // is not web request
			return;
		}

		if (self::$isOverflowed) { // header size limit already reached
			return;
		}

		self::init();

		self::$processedObjects = [];

		$row = [
			'label' => $label,
			'message' => $message,
			'extra' => self::getRowExtra(),
			'level' => $level
		];

		self::$json['rows'][] = self::format($row);
		$data = self::encode(self::$json);

		if (strlen($data) > self::$HEADERS_SIZE_LIMIT) { // check overflow
			$row = self::format([
				'message' => 'Incomplete logs, header size limit reached',
				'extra' => [],
				'level' => LogLevel::WARNING
			]);
			self::$json['rows'][ count(self::$json['rows']) - 1 ] = $row;
			$data = self::encode(self::$json);

			self::$isOverflowed = true;
		}

		if (!headers_sent()) {
			header(sprintf('%s: %s', self::$HEADER_NAME, $data));
		}
	}

	/**
	 * Запись стека вызовов функций.
	 *
	 * @param  string  $label  Заголовок
	 */
	public static function trace(string $label): void
	{
		self::write(LogLevel::DEBUG, $label, self::getTextTrace());
	}

	/**
	 * Запись строкового представления выброшенного объекта.
	 *
	 * @param  Throwable  $E  Выброшенный объект
	 */
	public static function logExceptionInfo(Throwable $E): void
	{
		self::write(LogLevel::ERROR, '', (string) $E);
	}
}
