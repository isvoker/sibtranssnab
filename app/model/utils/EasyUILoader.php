<?php
/**
 * Загрузчик ресурсов jQuery EasyUI [1.9+].
 *
 * @author Dmitriy Lunin
 */
class EasyUILoader
{
    /** Относительные пути к файлам ресурсов */
    protected const JS_PATH = Cfg::STATIC_RESOURCES_URL_PREFIX . 'js/ext/easyui/';
    protected const CSS_PATH = Cfg::STATIC_RESOURCES_URL_PREFIX . 'css/ext/easyui/';

    /** Предустановленные плагины */
    protected const PLUGINS = [
        'main' => [
            'js' => 'jquery.easyui.min.js',
            'css' => 'easyui.css'
        ],
        'draggable' => [
            'js' => 'plugins/jquery.draggable.js'
        ],
        'droppable' => [
            'js' => 'plugins/jquery.droppable.js'
        ],
        'resizable' => [
            'js' => 'plugins/jquery.resizable.js'
        ],
        'linkbutton' => [
            'js' => 'plugins/jquery.linkbutton.js',
            'css' => 'linkbutton.css'
        ],
        'progressbar' => [
            'js' => 'plugins/jquery.progressbar.js',
            'css' => 'progressbar.css'
        ],
        'tooltip' => [
            'js' => 'plugins/jquery.tooltip.js',
            'css' => 'tooltip.css'
        ],
        'pagination' => [
            'js' => 'plugins/jquery.pagination.js',
            'css' => 'pagination.css',
            'dependencies' => ['linkbutton']
        ],
        'datagrid' => [
            'js' => 'plugins/jquery.datagrid.js',
            'css' => 'datagrid.css',
            'dependencies' => ['panel', 'resizable', 'linkbutton', 'pagination']
        ],
        'treegrid' => [
            'js' => 'plugins/jquery.treegrid.js',
            'css' => 'tree.css',
            'dependencies' => ['datagrid']
        ],
        'propertygrid' => [
            'js' => 'plugins/jquery.propertygrid.js',
            'css' => 'propertygrid.css',
            'dependencies' => ['datagrid']
        ],
        'datalist' => [
            'js' => 'plugins/jquery.datalist.js',
            'css' => 'datalist.css',
            'dependencies' => ['datagrid']
        ],
        'panel' => [
            'js' => 'plugins/jquery.panel.js',
            'css' => 'panel.css'
        ],
        'window' => [
            'js' => 'plugins/jquery.window.js',
            'css' => 'window.css',
            'dependencies' => ['resizable', 'draggable', 'panel']
        ],
        'dialog' => [
            'js' => 'plugins/jquery.dialog.js',
            'css' => 'dialog.css',
            'dependencies' => ['linkbutton', 'window']
        ],
        'messager' => [
            'js' => 'plugins/jquery.messager.js',
            'css' => 'messager.css',
            'dependencies' => ['linkbutton', 'dialog', 'progressbar']
        ],
        'layout' => [
            'js' => 'plugins/jquery.layout.js',
            'css' => 'layout.css',
            'dependencies' => ['resizable', 'panel']
        ],
        'form' => [
            'js' => 'plugins/jquery.form.js'
        ],
        'menu' => [
            'js' => 'plugins/jquery.menu.js',
            'css' => 'menu.css'
        ],
        'tabs' => [
            'js' => 'plugins/jquery.tabs.js',
            'css' => 'tabs.css',
            'dependencies' => ['panel', 'linkbutton']
        ],
        'menubutton' => [
            'js' => 'plugins/jquery.menubutton.js',
            'css' => 'menubutton.css',
            'dependencies' => ['linkbutton', 'menu']
        ],
        'splitbutton' => [
            'js' => 'plugins/jquery.splitbutton.js',
            'css' => 'splitbutton.css',
            'dependencies' => ['linkbutton', 'menu']
        ],
        'switchbutton' => [
            'js' => 'plugins/jquery.switchbutton.js',
            'css' => 'switchbutton.css'
        ],
        'accordion' => [
            'js' => 'plugins/jquery.accordion.js',
            'css' => 'accordion.css',
            'dependencies' => ['panel']
        ],
        'calendar' => [
            'js' => 'plugins/jquery.calendar.js',
            'css' => 'calendar.css'
        ],
        'textbox' => [
            'js' => 'plugins/jquery.textbox.js',
            'css' => 'textbox.css',
            'dependencies' => ['validatebox', 'linkbutton']
        ],
        'passwordbox' => [
            'js' => 'plugins/jquery.passwordbox.js',
            'css' => 'passwordbox.css',
            'dependencies' => ['textbox']
        ],
        'filebox' => [
            'js' => 'plugins/jquery.filebox.js',
            'css' => 'filebox.css',
            'dependencies' => ['textbox']
        ],
        'radiobutton' => [
            'js' => 'plugins/jquery.radiobutton.js',
            'css' => 'radiobutton.css'
        ],
        'checkbox' => [
            'js' => 'plugins/jquery.checkbox.js',
            'css' => 'checkbox.css'
        ],
        'sidemenu' => [
            'js' => 'plugins/jquery.sidemenu.js',
            'css' => 'sidemenu.css',
            'dependencies' => ['accordion', 'tree', 'tooltip']
        ],
        'combo' => [
            'js' => 'plugins/jquery.combo.js',
            'css' => 'combo.css',
            'dependencies' => ['panel', 'textbox']
        ],
        'combobox' => [
            'js' => 'plugins/jquery.combobox.js',
            'css' => 'combobox.css',
            'dependencies' => ['combo']
        ],
        'combotree' => [
            'js' => 'plugins/jquery.combotree.js',
            'dependencies' => ['combo', 'tree']
        ],
        'combogrid' => [
            'js' => 'plugins/jquery.combogrid.js',
            'dependencies' => ['combo', 'datagrid']
        ],
        'combotreegrid' => [
            'js' => 'plugins/jquery.combotreegrid.js',
            'dependencies' => ['combo', 'treegrid']
        ],
        'tagbox' => [
            'js' => 'plugins/jquery.tagbox.js',
            'dependencies' => ['combobox']
        ],
        'validatebox' => [
            'js' => 'plugins/jquery.validatebox.js',
            'css' => 'validatebox.css',
            'dependencies' => ['tooltip']
        ],
        'numberbox' => [
            'js' => 'plugins/jquery.numberbox.js',
            'dependencies' => ['textbox']
        ],
        'searchbox' => [
            'js' => 'plugins/jquery.searchbox.js',
            'css' => 'searchbox.css',
            'dependencies' => ['menubutton', 'textbox']
        ],
        'spinner' => [
            'js' => 'plugins/jquery.spinner.js',
            'css' => 'spinner.css',
            'dependencies' => ['textbox']
        ],
        'numberspinner' => [
            'js' => 'plugins/jquery.numberspinner.js',
            'dependencies' => ['spinner', 'numberbox']
        ],
        'timespinner' => [
            'js' => 'plugins/jquery.timespinner.js',
            'dependencies' => ['spinner']
        ],
        'timepicker' => [
            'js' => 'plugins/jquery.timepicker.js',
            'css' => 'timepicker.css',
            'dependencies' => ['combo']
        ],
        'tree' => [
            'js' => 'plugins/jquery.tree.js',
            'css' => 'tree.css',
            'dependencies' => ['draggable', 'droppable']
        ],
        'datebox' => [
            'js' => 'plugins/jquery.datebox.js',
            'css' => 'datebox.css',
            'dependencies' => ['calendar', 'combo']
        ],
        'datetimebox' => [
            'js' => 'plugins/jquery.datetimebox.js',
            'dependencies' => ['datebox', 'timespinner']
        ],
        'slider' => [
            'js' => 'plugins/jquery.slider.js',
            'css' => 'slider.css',
            'dependencies' => ['draggable']
        ],
        'required' => [
            'js' => 'plugins/jquery.parser.js',
            'css' => 'flex.css'
        ]
    ];

    /** Предустановленные расширения функциональности */
    protected const EXTENSIONS = [
        'etree' => [
            'js' => 'extensions/jquery.etree.min.js',
            'dependencies' => ['tree', 'messager']
        ],
        'edatagrid' => [
            'js' => 'extensions/jquery.edatagrid.min.js',
            'dependencies' => ['datagrid', 'messager']
        ],
        'datagrid-detailview' => [
            'js' => 'extensions/datagrid-detailview.min.js',
            'dependencies' => ['datagrid']
        ],
        'datagrid-filter' => [
            'js' => 'extensions/datagrid-filter.min.js',
            'dependencies' => ['datagrid']
        ],
        'datagrid-groupview' => [
            'js' => 'extensions/datagrid-groupview.min.js',
            'dependencies' => ['datagrid']
        ]
    ];

    /** Предустановленные локализации */
    protected const LOCALES = [
        'en' => 'locale/easyui-lang-en.js',
        'ru' => 'locale/easyui-lang-ru.js'
    ];

    /**
     * Используемая "тема" оформления.
     *
     * @var string
     */
    protected static $theme = Cfg::UI_EASYUI_THEME;

    /**
     * Используемая локализация.
     *
     * @var string
     */
    protected static $locale = 'ru';

    /**
     * Надо подключать файлы для поддержки мобильных устройств.
     *
     * @var bool
     */
    protected static $withMobile = true;

    /**
     * Файл, содержащий код всех плагинов, УЖЕ подключен.
     *
     * @var bool
     */
    protected static $allPluginsIsLoaded = false;

    /**
     * Базовые файлы, необходимые для подключения
     * только отдельных плагинов, УЖЕ подключены.
     *
     * @var bool
     */
    protected static $baseFilesIsLoaded = false;

    /**
     * Установка используемой "темы" оформления. Одноимённая директория
     * должна располагаться внутри [[self::CSS_PATH]].
     *
     * @param  string  $theme
     */
    public static function setTheme(string $theme): void
    {
        if (
            $theme
            && is_dir( FsDirectory::normalizePath(Cfg::DIR_ROOT . self::CSS_PATH . $theme . Cfg::DS) )
        ) {
            self::$theme = $theme;
        }
    }

    /**
     * Установка используемой локализации
     * (из числа перечисленных в [[self::LOCALES]]).
     *
     * @param  string  $locale
     */
    public static function setLocale(string $locale): void
    {
        if (isset(self::LOCALES[ $locale ])) {
            self::$locale = $locale;
        }
    }

    /**
     * Подключение файлов локализации.
     * Следует выполнять ПОСЛЕ подключения прочих файлов.
     */
    public static function putLocale(): void
    {
        StaticResourceImporter::jsDirect(
            self::JS_PATH . self::LOCALES[ self::$locale ],
            ['posit' => StaticResourceImporter::RESOURCE_POSITION_LAST]
        );
    }

    /**
     * Установка флага, указывающего на необходимость подключения
     * специальных файлов для поддержки мобильных устройств.
     *
     * @param  bool  $withMobile
     */
    public static function setWithMobile(bool $withMobile): void
    {
        self::$withMobile = $withMobile;
    }

    /**
     * Подключение специальных файлов для поддержки мобильных устройств.
     */
    public static function putMobile(): void
    {
        StaticResourceImporter::cssDirect(self::CSS_PATH . 'mobile.css');
        StaticResourceImporter::jsDirect(self::JS_PATH . 'plugins/jquery.mobile.js');
        // имеются зависимости от отдельных плагинов...
    }

    /**
     * Подготовка к подключению только отдельных плагинов.
     */
    public static function initLazyLoading(): void
    {
        if (
            self::$allPluginsIsLoaded
            || self::$baseFilesIsLoaded
        ) {
            return;
        }

        StaticResourceImporter::cssDirect(self::CSS_PATH . 'icon.css');
        StaticResourceImporter::cssDirect(self::CSS_PATH . self::$theme . '/' . self::PLUGINS['required']['css']);
        StaticResourceImporter::jsDirect(self::JS_PATH . self::PLUGINS['required']['js']);

        //if (self::$withMobile) {
        //	self::putMobile();
        //}

        self::$baseFilesIsLoaded = true;
    }

    /**
     * Подключение всех стандартных плагинов.
     */
    public static function putAllPlugins(): void
    {
        if (!self::$allPluginsIsLoaded) {
            self::$allPluginsIsLoaded = true;

            StaticResourceImporter::cssDirect(self::CSS_PATH . self::$theme . '/easyui.css');
            StaticResourceImporter::cssDirect(self::CSS_PATH . 'icon.css');
            StaticResourceImporter::jsDirect(self::JS_PATH . 'jquery.easyui.min.js');

            if (self::$withMobile) {
                self::putMobile();
            }

            self::putLocale();
        }
    }

    /**
     * Подключение плагинов: добавление на страницу сайта
     * необходимых JS- и CSS-файлов.
     *
     * @param  array  $plugins     Список плагинов, которые нужно подключить
     * @param  bool   $singleLoad  Не надо загружать зависимости
     */
    public static function putPlugins(array $plugins, bool $singleLoad = false): void
    {
        if (self::$allPluginsIsLoaded) {
            return;
        }

        self::initLazyLoading();

        $allNecessary = $plugins;

        if (!$singleLoad) {
            $dependencies = [];
            foreach ($plugins as $plugin) {
                $dependencies[] = self::findDependencies($plugin);
            }
            $allNecessary = array_merge($allNecessary, ...$dependencies);
        }

        $allNecessary = array_reverse(array_unique($allNecessary));

        foreach ($allNecessary as $plugin) {
            if (isset(self::PLUGINS[ $plugin ])) {
                StaticResourceImporter::jsDirect(self::JS_PATH . self::PLUGINS[ $plugin ]['js']);
                if (isset(self::PLUGINS[ $plugin ]['css'])) {
                    StaticResourceImporter::cssDirect(
                        self::CSS_PATH . self::$theme . '/' . self::PLUGINS[ $plugin ]['css']
                    );
                }
            }
        }

        self::putLocale();
    }

    /**
     * Подключение расширений.
     *
     * @param  array  $extensions  Список необходимых расширений
     */
    public static function putExtensions(array $extensions): void
    {
        $plugins = $validExtensions = [];

        foreach ($extensions as $extension) {
            if (
                is_string($extension)
                && isset(self::EXTENSIONS[ $extension ])
            ) {
                if (isset(self::EXTENSIONS[ $extension ]['dependencies'])) {

                    foreach (self::EXTENSIONS[ $extension ]['dependencies'] as $dependency) {
                        $plugins[ $dependency ] = true;
                    }
                }

                $validExtensions[] = $extension;
            }
        }

        if ($plugins) {
            self::putPlugins( array_keys($plugins) );
        }

        foreach ($validExtensions as $extension) {
            StaticResourceImporter::jsDirect(self::JS_PATH . self::EXTENSIONS[ $extension ]['js']);
        }
    }

    /**
     * Рекурсивный поиск всех плагинов, от которых зависит заданный.
     *
     * @param   string  $plugin  Имя плагина
     * @return  array
     */
    protected static function findDependencies(string $plugin): array
    {
        $dependencies = [];
        if (isset(self::PLUGINS[ $plugin ]['dependencies'])) {
            foreach (self::PLUGINS[ $plugin ]['dependencies'] as $dependency) {
                $dependencies[] = [$dependency];
                $dependencies[] = self::findDependencies($dependency);
            }
            $dependencies = array_merge(...$dependencies);
        }

        return $dependencies;
    }
}
