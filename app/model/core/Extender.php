<?php
/**
 * Статичный класс Extender.
 *
 * Расширение базовой функциональности кодовой базы
 * путём вставки дополнительных исполняемых блоков.
 *
 * @author Dmitriy Lunin
 */
class Extender
{
	/**
	 * Каталог зарегистрированных callback-функций.
	 *
	 * @var array
	 */
	private static $addons = [];

	/**
	 * Регистрация callback-функции, вызываемой при заданном событий.
	 *
	 * @param  string    $event     Обозначение события
	 * @param  callable  $callback  Функция
	 */
	public static function add(string $event, callable $callback): void
	{
		self::$addons[ $event ][] = $callback;
	}

	/**
	 * Вызов зарегистрированных callback-функций.
	 *
	 * @param   string  $event         Обозначение события
	 * @param   mixed   $args[, ... ]  Передаваемые в функцию параметры
	 * @return  mixed   Результат вызова ПОСЛЕДНЕЙ callback-функции
	 */
	public static function call(string $event, ...$args)
	{
		$result = null;

		if (isset(self::$addons[ $event ])) {
			foreach (self::$addons[ $event ] as $addon) {
				$result = call_user_func_array($addon, $args);
			}
		}

		return $result;
	}
}
