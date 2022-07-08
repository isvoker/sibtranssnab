<?php

namespace Logger;

use Psr\Log\LogLevel;
use Throwable;

use Cfg;
use FsFile;
use Time;

/**
 * Поддержка логирования в файл.
 *
 * Запись ведётся в файл [[Cfg::DIR_LOGS]]${Y-m-d}.log
 *
 * @author Dmitriy Lunin
 */
class LogToFile extends AbstractLoggerHandler
{
	/** Имя обработчика */
	public const HANDLER = 'File';

	/**
	 * @see LogToInterface::isCompatible()
	 *
	 * @return bool
	 */
	public static function isCompatible(): bool
	{
		return true;
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
		FsFile::addTo(
			Cfg::DIR_LOG . Time::get(Time::SQL_DATE) . '.log',
			Time::getExactCurrentTime() . " [{$level}]" . ($label ? " {$label}\n" : "\n")
			. print_r($message, true)
			. "\n%------------------%\n"
		);
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
