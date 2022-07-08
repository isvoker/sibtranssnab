<?php

namespace Logger;

use Psr\Log\LogLevel;
use Cfg;
use Throwable;

/**
 * Поддержка логирования в stdout (на страницу сайта).
 *
 * Доступно для веб-запросов.
 *
 * @author Dmitriy Lunin
 */
class LogToHtml extends AbstractLoggerHandler
{
	/** Имя обработчика */
	public const HANDLER = 'Html';

	/** Соответствие типов сообщений фону HTML-блока */
	private static $LOG_LEVELS_STYLE = [
		LogLevel::DEBUG     => '#5AF',
		LogLevel::INFO      => '#3F3',
		LogLevel::NOTICE    => '#FA0',
		LogLevel::WARNING   => '#FA0',
		LogLevel::ERROR     => '#F44',
		LogLevel::CRITICAL  => '#F44',
		LogLevel::ALERT     => '#F44',
		LogLevel::EMERGENCY => '#F44'
	];

	/**
	 * @see LogToInterface::isCompatible()
	 *
	 * FALSE для НЕ веб-запросов.
	 *
	 * @return bool
	 */
	public static function isCompatible(): bool
	{
		return !in_array(\PHP_SAPI, ['cli', 'cli-server', 'phpdbg']);
	}

	/**
	 * Получение HTML-блока с сообщением.
	 *
	 * @param   string  $level    Тип сообщения
	 * @param   string  $label    Заголовок сообщения
	 * @param   mixed   $content  Тело сообщения
	 * @return  string
	 */
	private static function getHtmlBlock(string $level, string $label, string $content): string
	{
		return '<textarea readonly="readonly"'
			. ' style="background-color:'
			. (self::$LOG_LEVELS_STYLE[ $level ] ?? self::$LOG_LEVELS_STYLE[ LogLevel::NOTICE ])
			. ';color:#000;width:350px;height:250px">'
			. "{$label}:\n{$content}</textarea>";
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
		if ($level) {
			$label = '[' . $level . ']' . ($label ? ' ' . $label : '');
		}

		echo self::getHtmlBlock($level, $label, print_r($message, true));
	}

	/**
	 * Запись стека вызовов функций.
	 *
	 * @param  string  $label  Заголовок
	 */
	public static function trace(string $label): void
	{
		echo self::getHtmlBlock(LogLevel::DEBUG, $label, self::getTextTrace());
	}

	/**
	 * Запись строкового представления выброшенного объекта.
	 *
	 * @param  Throwable  $E  Выброшенный объект
	 */
	public static function logExceptionInfo(Throwable $E): void
	{
		echo '<pre>' . htmlspecialchars($E, ENT_NOQUOTES, Cfg::CHARSET) . '</pre>';
	}

	/**
	 * Получение строкового представления выброшенного объекта.
	 *
	 * @param   Throwable  $E  Выброшенный объект
	 * @return  string
	 */
	public static function getExceptionInfo(Throwable $E): string
	{
		return nl2br((string) $E);
	}
}
