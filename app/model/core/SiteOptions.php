<?php

/**
 * Статичный класс SiteOptions.
 *
 * Набор методов для работы с настройками сайта.
 *
 * @author Dmitriy Lunin
 */
class SiteOptions
{
	/** Таблица в БД, в которой хранятся настройки */
	public const DB_TABLE = Cfg::DB_TBL_PREFIX . 'site_options';

	/** Кэш настроек */
	private static $options = [];

	public const MAIN = 'main';
	public const SEO = 'seo';
	public const TPL = 'template';

	/**
	 * Загрузка значений настроек в кэш.
	 */
	public static function load(): void
	{
		$options = DBCommand::select([
			'select' => [['ident', 'value']],
			'from' => self::DB_TABLE
		]);

		foreach ($options as $option) {
			self::$options[$option['ident']] = $option['value'];
		}
	}

	/**
	 * Получение описания всех опций сайта.
	 *
	 * @param string $group
	 * @return array
	 */
	public static function getDeclaration(string $group): array
	{
		$options = DBCommand::select([
			'from' => self::DB_TABLE,
			'where' => $group ? ':group: = ' . DBCommand::qV($group) : '1',
			'order' => ['posit' => DBQueryBuilder::ASC],
		]);

		foreach ($options as $i => &$option) {
			if ($option['edit_mode'] === 'text') {
				$option['value'] = Html::qSC($option['value']);
			}
		}

		return $options;
	}

	/**
	 * Получение значения указанной опции сайта.
	 *
	 * @param string $ident Имя опции
	 * @return  string
	 */
	public static function get(string $ident): string
	{
		if (!isset(self::$options[$ident])) {
			Logger::error(__CLASS__, "Обращение к не существующей опции '{$ident}'");
		}

		return self::$options[$ident] ?? '';
	}

	/**
	 * Получение всех опций группы.
	 *
	 * @return  array
	 */
	public static function getAll(): array
	{
		return self::$options ?? [];
	}

	/**
	 * Применение изменения значения опции сайта с сохранением в БД.
	 *
	 * @param string $ident Имя опции
	 * @param string $value Значение опции
	 */
	private static function save(string $ident, string $value): void
	{
		self::$options[$ident] = $value;

		DBCommand::update(
			self::DB_TABLE,
			['value' => $value],
			':ident: = ' . DBCommand::qV($ident)
		);
	}

	/**
	 * Обновление массива опций сайта.
	 *
	 * @param array $options Опции, полученные из формы редактирования
	 */
	public static function set(array $options): void
	{
		foreach (self::$options as $ident => $value) {
			if (
				isset($options[$ident])
				&& $value !== $options[$ident]
			) {
				self::save($ident, $options[$ident]);
			}
		}
	}
}
