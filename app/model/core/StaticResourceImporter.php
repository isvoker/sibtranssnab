<?php
/**
 * Статичный класс StaticResourceImporter
 *
 * Обеспечение импорта статичных ресурсов (css/js) в страницу сайта
 *
 * @author Dmitriy Lunin
 */
class StaticResourceImporter
{
	/** Позиции подключаемых ресурсов */
    public const RESOURCE_POSITION_FIRST = 'f';
    public const RESOURCE_POSITION_LAST = 'l';

    /** Типы импортируемых ресурсов */
    private const RESOURCE_TYPE_CSS = 'css';
    private const RESOURCE_TYPE_JS = 'js';

	/** Исходная карта ресурсов */
    private const DEFAULT_MAP = [
		self::RESOURCE_TYPE_CSS => [],
		self::RESOURCE_TYPE_JS => []
	];

	/**
	 * Карта ресурсов вида ['css|js' => [
	 *   'имя-ресурса' => [
	 *     'path' => 'путь-к-файлу',
	 *     'isAbsolutePath' => true,
	 *   ],
	 * ]]
	 *
     * Без [['isAbsolutePath' => true]] путь считается относительно
     * [[Cfg::STATIC_RESOURCES_URL_PREFIX . (css|js)/]] .
	 **/
	private static $resourceMap = self::DEFAULT_MAP;

	/** Импортируемые в текущем сеансе ресурсы */
	private static $toImport = self::DEFAULT_MAP;

	/**
	 * Добавление карты ресурсов.
	 *
	 * @param  array   $newMap  Карта ресурсов
	 */
	public static function addMap(array $newMap): void
	{
		foreach (self::$resourceMap as $type => &$resources) {
			if (isset($newMap[ $type ]) && is_array($newMap[ $type ])) {
				foreach ($newMap[ $type ] as $resource => $options) {
					$resources[ $resource ] = $options;
				}
			}
		}
	}

	/**
	 * Добавление карты ресурсов заданного компонента.
	 * Файл карты должен располагаться в [[Cfg::DIR_RES_MAPS]] и иметь имя,
	 * совпадающее с названием компонента плюс расширение.
	 *
	 * @param  string  $component  Название компонента
	 */
	public static function addComponent(string $component): void
	{
		$resourceMap = require Cfg::DIR_RES_MAPS . $component . '.php';
		self::addMap($resourceMap);
	}

	/**
	 * Добавление карт классов всех подключенных модулей.
	 *
	 */
	public static function addModulesComponents(): void
	{
		$modules = BlockManager::getBlocksInFS();

		foreach($modules as $module) {
			self::addModuleComponent($module);
		}
	}

    /**
     * Добавление карты ресурсов заданного компонента.
     * Файл карты должен располагаться в [[Cfg::DIR_RES_MAPS]] и иметь имя,
     * совпадающее с названием компонента плюс расширение.
     *
     * @param  string  $module  Идентификатор модуля
     */
    public static function addModuleComponent(string $module): void
    {
        $resourceMap = require Cfg::DIR_MODULES . $module . '/resource_map.php';
        self::addMap($resourceMap);
    }

	/**
	 * Добавление заданного ресурса в список импортируемых.
	 *
	 * @param  string  $type     Тип ресурса
	 * @param  string  $path     Путь к ресурсу (url)
	 * @param  array   $options  Опциональные параметры импорта:
	 * ~~~
	 *   string  $position  Требуемая позиция подключаемого ресурса:
	 *                        - при self::RESOURCE_POSITION_FIRST ресурс будет подключён самым первым,
	 *                        - при self::RESOURCE_POSITION_LAST - последним.
	 * ~~~
	 */
	private static function importDirect(string $type, string $path, array $options = []): void
	{
		self::$toImport[ $type ][ $path ] = true;

		/*$position = $options['position'] ?? null;
		if ($position === null) {
			self::$toImport[ $type ][ $path ] = true;
		} elseif ($position === self::RESOURCE_POSITION_FIRST) {
			unset(self::$toImport[ $type ][ $path ]);
			arrayUnshiftAssoc(self::$toImport[ $type ], $path, true);
		} elseif ($position === self::RESOURCE_POSITION_LAST) {
			unset(self::$toImport[ $type ][ $path ]);
			self::$toImport[ $type ][ $path ] = true;
		}*/
	}

	/**
	 * Добавление заданного ресурса в список импортируемых.
	 *
	 * @param  string  $type      Тип ресурса
	 * @param  string  $resource  Имя ресурса, указанное в карте
	 * @param  array   $options   Опциональные параметры импорта:
	 * ~~~
	 *   string  $path            Путь к ресурсу (url)
	 *   bool    $isAbsolutePath  Абсолютен ли путь к ресурсу (относительно корня сайта либо внешний)?
	 * ~~~
	 * @throws  InternalEx
	 */
	private static function import(string $type, string $resource, array $options = []): void
	{
		if (isset(self::$resourceMap[ $type ][ $resource ])) {
			$options = empty($options)
				? self::$resourceMap[ $type ][ $resource ]
				: array_merge(self::$resourceMap[ $type ][ $resource ], $options);

			$path = $options['path'];
			if (!($options['isAbsolutePath'] ?? false)) {
                if ($options['module'] ?? false) {
                    $path = Cfg::STATIC_MODULES_URL_PREFIX . "{$options['module']}/tpl/{$type}/{$path}";
                } else {
                    $path = Cfg::STATIC_RESOURCES_URL_PREFIX . "{$type}/{$path}";
                }
			}

			self::importDirect($type, $path, $options);
		} else {
			throw new InternalEx( InternalEx::RESOURCE_NOT_DEFINED_IN_MAP, $resource );
		}
	}

	/**
	 * Добавление CSS-ресурса
	 *
	 * @param  string  $path     Путь к ресурсу (url)
	 * @param  array   $options  Опциональные параметры импорта
	 */
	public static function cssDirect(string $path, array $options = []): void
	{
		self::importDirect(self::RESOURCE_TYPE_CSS, $path, $options);
	}

	/**
	 * Добавление JS-ресурса
	 *
	 * @param  string  $path     Путь к ресурсу (url)
	 * @param  array   $options  Опциональные параметры импорта
	 */
	public static function jsDirect(string $path, array $options = []): void
	{
		self::importDirect(self::RESOURCE_TYPE_JS, $path, $options);
	}

	/**
	 * Добавление CSS-ресурса
	 *
	 * @param   string  $resource  Имя ресурса, указанное в карте
	 * @param   array   $options   Опциональные параметры импорта
	 * @throws  InternalEx
	 */
	public static function css(string $resource, array $options = []): void
	{
		self::import(self::RESOURCE_TYPE_CSS, $resource, $options);
	}

	/**
	 * Добавление JS-ресурса
	 *
	 * @param   string  $resource  Имя ресурса, указанное в карте
	 * @param   array   $options   Опциональные параметры импорта
	 * @throws  InternalEx
	 */
	public static function js(string $resource, array $options = []): void
	{
		self::import(self::RESOURCE_TYPE_JS, $resource, $options);
	}

	/**
	 * Получение списка импортируемых ресурсов.
	 *
	 * @return array
	 */
	public static function getResources(): array
	{
		return self::$toImport;
	}

	/**
     * Очистка списка импортируемых ресурсов.
     */
	public static function clearImport(): void
	{
		self::$toImport = self::DEFAULT_MAP;
	}

	/**
	 * Склеивание путей к подключаемым ресурсам для замены наборов файлов
	 * на их сжатые и объединённые средствами [[Minify]] варианты.
	 */
	public static function glueResources(): void
	{
		foreach (self::$toImport as $type => &$resources) {
			if (empty($resources)) {
				continue;
			}

			$comma = false;
			$gluedPath = Cfg::MINIFY_PATH;
			$lastFileRevision = '';

			foreach ($resources as $path => $flag) {
				if (
				    strpos($path, Cfg::STATIC_RESOURCES_URL_PREFIX) === 0
                    || strpos($path, Cfg::STATIC_MODULES_URL_PREFIX) === 0
                ) {
					unset($resources[ $path ]);

					// detect /path/file.v$REV.$EXT
					if (preg_match('/\.v(?<rev>[\d]{8}[\da-f])(?<ext>\.[\da-z]+)$/', $path, $matches)) {
						$path = substr($path, 0, -strlen($matches[0])) . $matches['ext'];

						if ($lastFileRevision < $matches['rev']) {
							$lastFileRevision = $matches['rev'];
						}
					}

					if ($comma) {
						$gluedPath .= ',';
					} else {
						$comma = true;
					}

					$gluedPath .= substr($path, 1);
				}
			}

			if ($comma) {
				if ($lastFileRevision !== '') {
					$gluedPath .= '&v=' . $lastFileRevision;
				}
				$resources[ $gluedPath ] = true;
			}
		}
	}
}
