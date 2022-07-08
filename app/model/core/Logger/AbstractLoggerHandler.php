<?php

namespace Logger;

use Logger;

/**
 * Базовая реализация типовых методов обработчика логирования.
 *
 * @author Dmitriy Lunin
 */
abstract class AbstractLoggerHandler implements LogToInterface
{
	/** Имя обработчика */
	public const HANDLER = '';

	/**
	 * Добавление обработчика.
	 *
	 * @param   bool  $forceEnable  Включить обработчик без проверки совместимости
	 * @return  bool  Включён ли обработчик
	 */
	public static function addHandler(bool $forceEnable = false): bool
	{
		return Logger::addHandler(static::HANDLER, $forceEnable);
	}

	/**
	 * Включение обработчика.
	 */
	public static function enable(): void
	{
		Logger::enableHandler(static::HANDLER);
	}

	/**
	 * Включение этого обработчика с выключением всех остальных.
	 */
	public static function enableOnlyIt(): void
	{
		Logger::disableAllHandlers();
		self::enable();
	}

	/**
	 * Отключение обработчика.
	 */
	public static function disable(): void
	{
		Logger::disableHandler(static::HANDLER);
	}

	/**
	 * Включён ли обработчик?
	 *
	 * @return bool
	 */
	public static function isEnabled(): bool
	{
		return Logger::isHandlerEnabled(static::HANDLER);
	}

	/**
	 * Получение стека вызовов функций в текстовом виде.
	 *
	 * @return string
	 */
	protected static function getTextTrace(): string
	{
		ob_start();
		debug_print_backtrace();
		$trace = ob_get_clean();
		return preg_replace('/^#.+Logger::trace\([^\n]+ called at/s', '##  ... called at', $trace, 1);
	}
}
