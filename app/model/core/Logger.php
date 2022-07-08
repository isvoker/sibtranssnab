<?php

use Psr\Log\LogLevel;

/**
 * Статичный класс Logger.
 *
 * Приоритет обработчиков соответствует порядку добавления.
 * В request-параметре "log" можно явно указать требуемый обработчик
 * из числа добавленных (напр., ?log=File).
 * Возможна отправка сообщений сразу нескольким обработчикам.
 * Некоторые медоты допускают явный строгий выбор обработчика.
 *
 * @author Dmitriy Lunin
 */
class Logger
{
	/** Доступные обработчики логирования */
	protected static $handlers = [];

	/** Перехваченные объекты, выброшенные с помощью выражения [[throw]] */
	protected static $exceptions = [];

	/**
	 * Добавление нового обработчика.
	 *
	 * @param   string  $handler    Обозначение обработчика
	 * @param   bool    $forceEnable  Включить обработчик без проверки совместимости
	 * @return  bool    Включён ли обработчик
	 */
	public static function addHandler(string $handler, bool $forceEnable = false): bool
	{
		self::$handlers[ $handler ] = $forceEnable
			|| call_user_func(['Logger\LogTo' . $handler, 'isCompatible']);

		return self::$handlers[ $handler ];
	}

	/**
	 * Включение обработчика.
	 *
	 * @param  string  $handler    Обозначение обработчика
	 */
	public static function enableHandler(string $handler): void
	{
		self::$handlers[ $handler ] = true;
	}

	/**
	 * Выключение обработчика.
	 *
	 * @param  string  $handler    Обозначение обработчика
	 */
	public static function disableHandler(string $handler): void
	{
		self::$handlers[ $handler ] = false;
	}

	/**
	 * Выключение всех доступных обработчиков логирования.
	 */
	public static function disableAllHandlers(): void
	{
		foreach (self::$handlers as $handler => $enabled) {
			self::$handlers[ $handler ] = false;
		}
	}

	/**
	 * Включен ли обработчик.
	 *
	 * @param   string  $handler    Обозначение обработчика
	 * @return  bool
	 */
	public static function isHandlerEnabled(string $handler): bool
	{
		return self::$handlers[ $handler ] ?? false;
	}

	/**
	 * Установка обработчиков ошибок
	 * и активация совместимых обработчиков логирования сообщений.
	 */
	public static function init(): void
	{
		self::setErrorsHandler();

		if ($requestedHandlers = Request::getVar('log', 'string')) {
			$requestedHandlers = [ $requestedHandlers ];
		} else {
			$requestedHandlers = Request::getVar('log', 'array');
		}

		if ($requestedHandlers) {
			foreach ($requestedHandlers as $requestedHandler) {
				if (
					is_string($requestedHandler)
					&& isset(self::$handlers[ $requestedHandler ])
				) {
					self::enableHandler($requestedHandler);
				}
			}

			return;
		}
	}

	/**
	 * Вызов заданного метода задействованных обработчиков.
	 *
	 * @param  string  $action      Метод ('write'|'trace'|'logExceptionInfo')
	 * @param  array   $args        Передаваемые методу параметры
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	protected static function sendToHandlers($action, array $args, string $useHandler = ''): void
	{
		if (
			$useHandler !== ''
			&& isset(self::$handlers[ $useHandler ])
		) {
			call_user_func_array(['Logger\LogTo' . $useHandler, $action], $args);
			return;
		}

		foreach (self::$handlers as $handler => $isEnabled) {
			if ($isEnabled) {
				call_user_func_array(['Logger\LogTo' . $handler, $action], $args);
			}
		}
	}

	/**
	 * Сообщение произвольного типа.
	 *
	 * @param  string  $level       Тип сообщения
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function log(string $level, string $label, $message, string $useHandler = ''): void
	{
		self::sendToHandlers('write', [$level, $label, $message], $useHandler);
	}

	/**
	 * Отладочная информация.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function debug(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::DEBUG, $label, $message, $useHandler);
	}

	/**
	 * Информационное сообщение.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function info(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::INFO, $label, $message, $useHandler);
	}

	/**
	 * Сообщение-уведомление.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function notice(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::NOTICE, $label, $message, $useHandler);
	}

	/**
	 * Сообщение-предупреждение.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function warning(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::WARNING, $label, $message, $useHandler);
	}

	/**
	 * Сообщение об ошибке.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function error(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::ERROR, $label, $message, $useHandler);
	}

	/**
	 * Сообщение о критической ошибке/ситуации.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function critical(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::CRITICAL, $label, $message, $useHandler);
	}

	/**
	 * Сообщение, требующее незамедлительной реакции.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function alert(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::ALERT, $label, $message, $useHandler);
	}

	/**
	 * Сообщение об аварии, приведшей к неработоспособности системы.
	 *
	 * @param  string  $label       Заголовок сообщения
	 * @param  mixed   $message     Тело сообщения
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function emergency(string $label = '', $message = null, string $useHandler = ''): void
	{
		self::log(LogLevel::EMERGENCY, $label, $message, $useHandler);
	}

	/**
	 * Отображение стека вызовов функций.
	 *
	 * @param  string  $label       Заголовок
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function trace(string $label = '', string $useHandler = ''): void
	{
		self::sendToHandlers('trace', [$label ?: 'Backtrace'], $useHandler);
	}

	/**
	 * Удобочитаемая информация о переменной.
	 *
	 * @param  mixed   $var         Выражение для вывода на экран
	 * @param  bool    $trace       Получить стек вызовов функций
	 * @param  string  $useHandler  Использовать только указанный обработчик
	 */
	public static function viewVar($var, bool $trace = false, string $useHandler = ''): void
	{
		$label = gettype($var);

		if ($var === null) {
			$var = '[[NULL]]';
		} elseif ($var === '') {
			$var = '[[empty string]]';
		} elseif ($var === true) {
			$var = '[[TRUE]]';
		} elseif ($var === false) {
			$var = '[[FALSE]]';
		}

		if ($trace) {
			self::trace('calling viewVar()', $useHandler);
		}

		self::debug($label, $var, $useHandler);
	}

	/**
	 * Пользовательский обработчик ошибок.
	 *
	 * @link  https://secure.php.net/manual/function.set-error-handler.php
	 *
	 * @param   int     $type      Уровень ошибки
	 * @param   string  $message   Сообщение об ошибке
	 * @param   string  $filename  Имя файла, в котором произошла ошибка
	 * @param   int     $lineno    Номер строки, в которой произошла ошибка
	 * @return  bool
	 */
	public static function errorHandler(int $type, string $message, string $filename, int $lineno): bool
	{
		if (!(error_reporting() & $type)) {
			// Этот код ошибки не включён в error_reporting,
			// так что пусть обрабатывается стандартным обработчиком ошибок PHP
			return false;
		}

		if (Cfg::DEBUG_IS_ON) {
			$errors = [
				E_ERROR             => 'E_ERROR',
				E_WARNING           => 'E_WARNING',
				E_PARSE             => 'E_PARSE',
				E_NOTICE            => 'E_NOTICE',
				E_CORE_ERROR        => 'E_CORE_ERROR',
				E_CORE_WARNING      => 'E_CORE_WARNING',
				E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
				E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
				E_USER_ERROR        => 'E_USER_ERROR',
				E_USER_WARNING      => 'E_USER_WARNING',
				E_USER_NOTICE       => 'E_USER_NOTICE',
				E_STRICT            => 'E_STRICT',
				E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
				E_DEPRECATED        => 'E_DEPRECATED',
				E_USER_DEPRECATED   => 'E_USER_DEPRECATED'
			];
			self::error(
				$errors[ $type ] ?? 'Unknown PHP error',
				"errno {$type}: {$message} ({$filename}:{$lineno})"
			);
		}

		return true;
	}

	/**
	 * Обработчик фатальных ошибок.
	 */
	public static function handleFatalError(): void
	{
		if (
			($error = error_get_last())
			&& $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)
		) {
			ob_end_clean();
			self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
		}
		if (ob_get_level() > 0) {
			ob_end_flush();
		}

		exit;
	}

	/**
	 * Установка обработчиков ошибок.
	 */
	public static function setErrorsHandler(): void
	{
		set_error_handler([__CLASS__, 'errorHandler'], E_ALL);
		register_shutdown_function([__CLASS__, 'handleFatalError']);
	}

	/**
	 * Получение сообщения выброшенного объекта.
	 *
	 * @param   Throwable  $E  Выброшенный объект
	 * @return  string
	 */
	public static function getError(Throwable $E): string
	{
		if ($E instanceof SenseiExceptionInterface) {
			return $E->getError();
		}

		if (Cfg::DEBUG_IS_ON) {
			return $E->getMessage();
		}

		return 'Ошибка';
	}

	/**
	 * Получение отладочной информации выброшенного объекта.
	 *
	 * @param   Throwable  $E  Выброшенный объект
	 * @return  string
	 */
	public static function getDebugInfo(Throwable $E): string
	{
		if (!Cfg::DEBUG_IS_ON) {
			return '';
		}

		return (string) $E;
	}

	/**
	 * Регистрация исключения.
	 *
	 * @param  Throwable  $E  Выброшенный объект
	 */
	public static function registerException(Throwable $E): void
	{
		self::$exceptions[] = $E;
	}

	/**
	 * Отображение сообщений и отладочной информации выброшенных [[throw]] объектов,
	 * перехваченных за время работы приложения.
	 */
	public static function showExceptions(): void
	{
		if (empty(self::$exceptions)) {
			return;
		}

		foreach (self::$exceptions as $E) {
			self::error('Exception', self::getError($E));

			if (Cfg::DEBUG_IS_ON) {
				self::sendToHandlers('logExceptionInfo', [$E]);
			}
		}
		self::$exceptions = [];
	}
}
