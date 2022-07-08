<?php
/**
 * Статичный класс ClassLoader.
 *
 * Загрузка файлов с реализациями классов и функций.
 *
 * @author Dmitriy Lunin
 */
class ClassLoader
{
	/** Расширение подключаемых файлов */
    public const FILE_EXT = '.php';

	/** Список уже загруженных файлов */
	private static $includedFiles = [];

	/** Карта классов - пары ['имя-класса' => 'путь-к-файлу-относительно-`Cfg::DIR_MODEL`'] */
	private static $classMap = [];

    /**
     * Подключение файла.
     *
     * @param   string  $path  Полный путь к файлу, за исключением расширения
     * @return  bool    Успешно ли выполнена операция
     */
    private static function includeFile(string $path): bool
    {
        $pathHash = md5($path);
        if (isset(self::$includedFiles[ $pathHash ])) {
            return true;
        }

        $path .= self::FILE_EXT;
        if (is_file($path) && is_readable($path)) {
            self::$includedFiles[ $pathHash ] = true;
            classLoaderIncludeFile($path);
            return true;
        }

        //throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, $path );
        return false;
    }

	/**
	 * Предзагрузка класса. Файл должен располагаться
	 * в стандартной директории классов в заданной поддиректории.
	 * Расширение указывать не нужно.
	 *
	 * @param   string  $classPath  Например, 'core/Application'
	 * @return  bool    Успешно ли выполнена операция
	 */
	public static function preloadClass(string $classPath): bool
	{
		return self::includeFile(Cfg::DIR_MODEL . $classPath);
	}

    /**
     * Предзагрузка класса. Файл должен располагаться
     * в директории модулей в заданной поддиректории.
     * Расширение указывать не нужно.
     *
     * @param   string  $module Идентификатор модуля
     * @param   string  $class  Имя класса
     * @return  bool    Успешно ли выполнена операция
     */
    public static function preloadModuleClass(string $module, string $class): bool
    {
        return self::includeFile(Cfg::DIR_MODULES . $module . Cfg::DS . $class);
    }

	/**
	 * Предзагрузка файла с описанием функций. Файл должен располагаться
	 * в стандартной директории функций и иметь указанное имя.
	 * Расширение указывать не нужно.
	 *
	 * @param   string  $type  Название файла с функциями
	 * @return  bool    Успешно ли выполнена операция
	 */
	public static function preloadFunctions(string $type): bool
	{
		return self::includeFile(Cfg::DIR_FUNCTIONS . $type);
	}

	/**
	 * Загрузка класса из числа находящихся в карте.
	 * Используется в качестве реализации метода __autoload().
	 *
	 * @param   string  $className  Например, 'core\Application'
	 * @return  bool    Успешно ли выполнена операция
	 */
	public static function loadClass(string $className): bool
	{
        if (isset(self::$classMap[ $className ])) {
            return self::includeFile(Cfg::DIR_MODEL . self::$classMap[ $className ]);
        }

        return false;
	}

    /**
     * Загрузка класса из числа находящихся в карте.
     * Используется в качестве реализации метода __autoload().
     *
     * @param   string  $className  Например, 'core\Application'
     * @return  bool    Успешно ли выполнена операция
     */
    public static function loadModuleClass(string $className): bool
    {
        if (isset(self::$classMap[ $className ])) {
            return self::includeFile(Cfg::DIR_MODULES . self::$classMap[ $className ]);

        }

        return false;
    }

	/**
	 * Добавление карты классов.
	 *
	 * @param  array  $classMap  Карта классов
	 */
	public static function addMap(array $classMap): void
	{
		if (self::$classMap) {
			self::$classMap = array_merge(self::$classMap, $classMap);
		} else {
			self::$classMap = $classMap;
		}
	}

	/**
	 * Добавление карты классов заданного компонента.
	 * Файл карты должен располагаться в [[Cfg::DIR_MODEL_MAPS]] и иметь имя,
	 * совпадающее с названием компонента плюс расширение.
	 *
	 * @param  string  $component  Название компонента
	 */
	public static function addComponent(string $component): void
	{
        $classMap = require Cfg::DIR_MODEL_MAPS . $component . self::FILE_EXT;
        self::addMap($classMap);
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
     * Добавление карты классов заданного модуля.
     * Файл карты должен располагаться в [[Cfg::DIR_MODULES]]/{$module} и иметь имя class_map.{self::FILE_EXT}
     *
     * @param   string  $module  Идентификатор модуля
     */
    public static function addModuleComponent(string $module): void
    {
    	$map_path = Cfg::DIR_MODULES . $module . Cfg::DS . 'class_map' . self::FILE_EXT;

    	if (FsFile::isExists($map_path)) {
		    self::addMap(require $map_path);
	    }
    }

	/**
	 * Регистрация реализации метода __autoload().
	 *
	 * @param   bool  $prepend  Регистрировать ли реализацию как первоочередную
	 * @return  bool  Успешно ли выполнена операция
	 */
	public static function register(bool $prepend = true): bool
	{
		$model = spl_autoload_register([__CLASS__, 'loadClass'], true, $prepend);
        $modules = spl_autoload_register([__CLASS__, 'loadModuleClass'], true, false);

        return $model && $modules;
    }

	/**
	 * Отмена регистрации реализации метода __autoload().
	 *
	 * @return  bool  Успешно ли выполнена операция
	 */
	public static function unregister(): bool
	{
		$model = spl_autoload_unregister([__CLASS__, 'loadClass']);
		$modules = spl_autoload_unregister([__CLASS__, 'loadModuleClass']);

		return $model && $modules;
	}

	/** Инициализация автозагрузки классов */
	public static function init(): void
	{
		self::addComponent('application');
		self::register();
	}
}

/**
 * Подключение файла в изолированной области видимости
 * с целью предотвращения доступа к $this/self.
 *
 * @param  string  $path  Полный путь к файлу
 */
function classLoaderIncludeFile($path): void
{
	include $path;
}
