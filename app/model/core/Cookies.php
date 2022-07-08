<?php
/**
 * Статичный класс Cookies.
 *
 * Класс для работы с HTTP cookies.
 *
 * @author Dmitriy Lunin
 */
class Cookies
{
	/** Параметры cookie по умолчанию */
	protected static $defaultCookie = [
		'name'     => null,
		'value'    => '',
		'expire'   => 0,
		'path'     => '/',
		'domain'   => '',
		'secure'   => false,
		'httpOnly' => true
	];

	/** Cookie, переданные сайту браузером в заголовках запроса */
	protected static $requestCookies = [];

	/** Cookie, которые будут переданы сайтом браузеру в заголовках ответа */
	protected static $responseCookies = [];

	/**
	 * Получение из $_COOKIE всех cookie, отправленных браузером.
	 *
	 * @return array
	 */
	public static function loadRequestCookies(): array
	{
		self::$defaultCookie['secure'] = Request::isSecureConnection();
		self::$requestCookies = [];

		foreach ($_COOKIE as $name => $value) {
			self::$requestCookies[ $name ] = self::$defaultCookie;
			self::$requestCookies[ $name ]['name'] = $name;
			self::$requestCookies[ $name ]['value'] = $value;
		}

		return self::$requestCookies;
	}

	/**
	 * Добавление cookie.
	 *
	 * @param   array  $cookie  Массив с параметрами cookie
	 * @return  bool   Успешно ли выполнена операция
	 */
	public static function add(array $cookie): bool
	{
		if (!isset($cookie['name']) || !is_string($cookie['name'])) {
			return false;
		}

		$defaultCookie = self::$requestCookies[ $cookie['name'] ] ?? self::$defaultCookie;
		self::$responseCookies[ $cookie['name'] ] = array_merge($defaultCookie, $cookie);

		return true;
	}

	/**
	 * Получение cookie для ответа сервера.
	 *
	 * @return array
	 */
	public static function getResponseCookies(): array
	{
		return self::$responseCookies;
	}

	/**
	 * Получение cookie с заданным наименованием.
	 *
	 * @param   string  $name  Наименование cookie
	 * @return  array
	 */
	public static function get(string $name): array
	{
		return self::$responseCookies[ $name ] ?? self::$requestCookies[ $name ] ?? [];
	}

	/**
	 * Получение значения cookie с заданным наименованием.
	 *
	 * @param   string  $name          Наименование cookie
	 * @param   mixed   $defaultValue  Значение по умолчанию на случай, если cookie не существует
	 * @return  mixed
	 */
	public static function getValue(string $name, $defaultValue = null)
	{
		$cookie = self::get($name);

		if (!empty($cookie)) {
			return $cookie['value'];
		}

		return $defaultValue;
	}

	/**
	 * Удаление cookie с заданным наименованием.
	 *
	 * @param  string  $name  Наименование cookie
	 */
	public static function remove(string $name): void
	{
		if (!isset(self::$responseCookies[ $name ])
			&& !isset(self::$requestCookies[ $name ])) {
			return;
		}

		if (!isset(self::$responseCookies[ $name ])) {
			self::$responseCookies[ $name ] = self::$requestCookies[ $name ];
		}

		self::$responseCookies[ $name ]['expire'] = 1;
	}

	/** Удаление всех cookie */
	public static function removeAll(): void
	{
		self::$responseCookies = self::$requestCookies;
		foreach (self::$responseCookies as $name => &$cookie) {
			$cookie['expire'] = 1;
		}
	}
}
