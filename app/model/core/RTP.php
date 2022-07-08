<?php


class RTP
{
	private static $runTimeParameters = [];

	private const PAGE_NAME = 'pageName';
	private const PAGE_TITLE = 'pageTitle';
	private const KEYWORDS = 'keywords';
	private const DESCRIPTION = 'description';
	private const NOINDEX = 'noindex';

	/**
	 * Получение некоторого параметра. Если параметр не существует, возвращается NULL.
	 *
	 * @param   string  $name  Название параметра
	 * @return  mixed
	 */
	public static function get(string $name)
	{
		return self::$runTimeParameters[$name] ?? null;
	}

	/**
	 * Получение списка параметров.
	 *
	 * @return  array
	 */
	public static function getAll(): array
	{
		return self::$runTimeParameters;
	}

	/**
	 * Установка некоторого параметра.
	 *
	 * @param  string  $name      Название параметра
	 * @param  mixed   $value     Значение параметра
	 * @param  bool    $encode    Преобразовать специальные символы в HTML-сущности
	 * @param  bool    $override  Перезаписать ранее установленное значение
	 */
	public static function set(string $name, $value, bool $encode = true, bool $override = true): void
	{
		if (!$override && isset(self::$runTimeParameters[ $name ])) {
			return;
		}

		if (is_string($value) && $encode) {
			$value = Html::qSC($value);
		}

		self::$runTimeParameters[ $name ] = $value;
	}

	public static function setPageName(string $value): void
	{
		self::set(self::PAGE_NAME, $value);
	}

	public static function setPageTitle(string $value, bool $override = true): void
	{
		self::set(self::PAGE_TITLE, $value, true, $override);
	}

	public static function setKeywords(string $value): void
	{
		self::set(self::KEYWORDS, $value);
	}

	public static function setDescription(string $value): void
	{
		self::set(self::DESCRIPTION, $value);
	}

	public static function setNoindex(bool $value = true): void
	{
		self::set(self::NOINDEX, $value);
	}
}
