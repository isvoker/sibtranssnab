<?php

namespace Logger;

use Psr\Log\LogLevel;
use Throwable;
use FirePHP;

\ClassLoader::preloadClass('vendor/firephp/firephp-core/FirePHP.class');

/**
 * Поддержка логирования в консоль браузера по протоколу FirePHP.
 *
 * Доступно для браузеров, отправляющих HTTP-заголовок "X-FirePHP-Version"
 * или содержащих строку /\sFirePHP\/[.\d]*\s?/ в "User-Agent".
 *
 * @see https://firephp.org/
 * @author Dmitriy Lunin
 */
class LogToFirePHP extends AbstractLoggerHandler
{
	/** Имя обработчика */
	public const HANDLER = 'FirePHP';

	/** FirePHP instance */
	protected static $FirePHP;

	/**
	 * @see LogToInterface::isCompatible()
	 *
	 * Проверка на основе значения HTTP-заголовков запроса.
	 *
	 * @return bool
	 */
	public static function isCompatible(): bool
	{
		$header = $_SERVER['HTTP_X_FIREPHP_VERSION'] ?? null;
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

		return (
				( $header&& preg_match('/^([.\d]*)$/', $header, $matches) )
				|| ( $userAgent && preg_match('/\sFirePHP\/([.\d]*)\s?/i', $userAgent, $matches) )
			)
			&& version_compare($matches[1], '0.0.6', '>=');
	}

	/**
	 * Активация обработчика.
	 */
	public static function init(): void
	{
		if (self::$FirePHP === null) {
			self::$FirePHP = FirePHP::getInstance(true);
			//self::$FirePHP->registerErrorHandler(true);
			//self::$FirePHP->registerExceptionHandler();
		}
	}

	/**
	 * Отключение обработчика.
	 */
	public static function disable(): void
	{
		self::$FirePHP = null;
		parent::disable();
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
		self::init();

		switch ($level) {
			case LogLevel::INFO:
				$type = FirePHP::INFO;
				break;

			case LogLevel::WARNING:
				$type = FirePHP::WARN;
				break;

			case LogLevel::ERROR:
				$type = FirePHP::ERROR;
				break;

			case LogLevel::EMERGENCY:
				$type = FirePHP::EXCEPTION;
				break;

			default:
				$type = FirePHP::LOG;
				break;
		}

		if (empty($label) && $message === null) {
			$message = "[{$type}]";
		}

		self::$FirePHP->fb($message, $label, $type);
	}

	/**
	 * Запись стека вызовов функций.
	 *
	 * @param  string  $label  Заголовок
	 */
	public static function trace(string $label): void
	{
		self::$FirePHP->setOptions(['useNativeJsonEncode' => false]);
		self::$FirePHP->trace($label);
		self::$FirePHP->setOptions(['useNativeJsonEncode' => true]);
	}

	/**
	 * Запись строкового представления выброшенного объекта.
	 *
	 * @param  Throwable  $E  Выброшенный объект
	 */
	public static function logExceptionInfo(Throwable $E): void
	{
		self::write(LogLevel::EMERGENCY, '', (string) $E);
	}
}
