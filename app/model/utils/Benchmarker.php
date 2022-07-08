<?php
/**
 * Статичный класс Benchmarker.
 *
 * Предоставление различной технической информации.
 *
 * @author Dmitriy Lunin
 */
class Benchmarker
{
	/**
	 * Получение информации о среде выполнения сайта.
	 *
	 * @return array
	 */
	public static function getSiteInfo(): array
	{
		return [
			[
				'name' => 'IP адрес сайта и порт',
			 	'value' => Request::getServerIP()
			],
			[
				'name' => 'Серверное время',
				'value' => Time::toSQLDateTime()
			],
			[
				'name' => 'Операционная система',
				'value' => PHP_OS
			],
			[
				'name' => 'Веб-сервер',
				'value' => Request::getServerVar('SERVER_SOFTWARE')
			],
			[
				'name' => 'Протокол',
				'value' => Request::getServerVar('SERVER_PROTOCOL')
			],
			[
				'name' => 'Поддержка сжатия',
				'value' => Request::getServerVar('HTTP_ACCEPT_ENCODING')
			],
			[
				'name' => 'Версия PHP',
				'value' => PHP_VERSION
			],
			[
				'name' => 'Серверное API',
				'value' => PHP_SAPI
			],
			[
				'name' => 'Сервер БД',
				'value' => DBCommand::getHostInfo()
			],
			[
				'name' => 'Версия сервера БД',
				'value' => DBCommand::getServerInfo()
			],
			[
				'name' => 'Обнаружение попыток взлома',
				'value' => boolToStr(Cfg::GUARD_IS_ON)
			],
			[
				'name' => 'Компрессия JS- и CSS-файлов',
				'value' => boolToStr(Cfg::MINIFY_IS_ON)
			],
			[
				'name' => 'Режим отладки',
				'value' => boolToStr(Cfg::DEBUG_IS_ON)
			],
			[
				'name' => 'Администратор сервера',
				'value' => Request::getServerVar('SERVER_ADMIN')
			]
		];
	}

	/**
	 * Преобразование количества микросекунд в "human readable" вид.
	 *
	 * @param   float   $microtime  Исходное количество микросекунд
	 * @param  ?string  $format     Совместимый с printf() формат отображения
	 * @param   int     $precision  Точность округления (количество знаков после запятой)
	 * @return  string
	 */
	public static function readableElapsedTime(float $microtime, string $format = null, int $precision = 3): string
	{
		if (is_null($format)) {
			$format = '%.3f%s';
		}

		if ($microtime >= 1) {
			$unit = 's';
			$time = round($microtime, $precision);
		} else {
			$unit = 'ms';
			$time = round($microtime * 1000);

			$format = preg_replace('/(%.[\d]+f)/', '%d', $format);
		}

		return sprintf($format, $time, $unit);
	}

	/**
	 * Преобразование количества байт в "human readable" вид.
	 *
	 * @param   int  $bytes      Исходное количество байт
	 * @param   int  $precision  Требуемая точность результата (количество знаков после запятой)
	 * @return  string
	 */
	public static function readableSize(int $bytes, int $precision = 4): string
	{
		$units = ['B', 'Kb', 'Mb', 'Gb', 'Tb'];

		$bytes = max($bytes, 0);
		$pow = floor( ($bytes ? log($bytes) : 0) / log(1024) );
		$pow = min($pow, count($units) - 1);

		$bytes /= (1 << (10 * $pow));

		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	/**
	 * Замер времени выполнения функции.
	 *
	 * @param   callable  $callable   Вызываемая функция
	 * @param   array     $arguments  Передаваемые в функцию параметры в виде индексированного массива
	 * @param   int       $runs       Количество запусков функции
	 * @return  string
	 */
	public static function testFunctionSpeed(callable $callable, array $arguments = [], int $runs = 1): string
	{
		$startTime = microtime(true);

		for ($i = 0; $i < $runs; ++$i) {
			call_user_func_array($callable, $arguments);
		}

		$endTime = microtime(true);

		return self::readableElapsedTime($endTime - $startTime);
	}

	/**
	 * Замер выделенной на выполнение функции памяти.
	 *
	 * @param   callable  $callable   Вызываемая функция
	 * @param   array     $arguments  Передаваемые в функцию параметры в виде индексированного массива
	 * @return  string
	 */
	public static function testMemoryUsage(callable $callable, array $arguments = []): string
	{
		call_user_func_array($callable, $arguments);
		return self::readableSize( memory_get_usage(true) );
	}

	/**
	 * Получение пикового значения выделенного объёма памяти.
	 *
	 * @return string
	 */
	public static function getMemoryPeak(): string
	{
		return self::readableSize( memory_get_peak_usage(true) );
	}

}
