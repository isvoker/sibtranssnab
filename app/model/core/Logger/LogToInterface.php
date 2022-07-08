<?php

namespace Logger;

use Throwable;

/**
 * Обязательные методы, которые должен реализовывать
 * обработчик логирования.
 *
 * @author Dmitriy Lunin
 */
interface LogToInterface
{
	/**
	 * Добавление обработчика.
	 *
	 * @param   bool  $forceEnable  Включить обработчик без проверки совместимости
	 * @return  bool  Включён ли обработчик
	 */
	public static function addHandler(bool $forceEnable = false): bool;

	/**
	 * Предполагает ли пользовательский запрос совместимость с данным обработчиком?
	 *
	 * @return bool
	 */
	public static function isCompatible(): bool;

	/**
	 * Запись сообщения.
	 *
	 * @param  string  $level    Тип сообщения
	 * @param  string  $label    Заголовок сообщения
	 * @param  mixed   $message  Тело сообщения
	 */
	public static function write(string $level, string $label, $message): void;

	/**
	 * Запись стека вызовов функций.
	 *
	 * @param  string  $label  Заголовок
	 */
	public static function trace(string $label): void;

	/**
	 * Запись строкового представления выброшенного объекта.
	 *
	 * @param  Throwable  $E  Выброшенный объект
	 */
	public static function logExceptionInfo(Throwable $E): void;
}
