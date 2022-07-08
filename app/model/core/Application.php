<?php

ClassLoader::loadClass('Module');
ClassLoader::loadClass('CPageMeta');

/**
 * Статичный класс Application.
 *
 * Отображение контента сайта.
 */
class Application
{
    /** Таблица в БД с главными шаблонами */
    public const DB_TABLE_MAIN_TEMPLATES = 'main_templates';

    /**
     * Имя переменной HTTP-запроса, содержащей обозначение
     * требуемой пользователю версии сайта
     */
    public const SITE_VERSION_REQUEST_VAR_NAME = 'toggle_site_version';

    /**
     * Наименование cookie для хранения обозначения
     * требуемой пользователю версии сайта
     */
    public const SITE_VERSION_COOKIE_NAME = 'application_view';

    /** Обозначение версия сайта для настольных устройств */
    private const SITE_VERSION_DESKTOP = 'desktop';

    /** Обозначение версия сайта для мобильных устройств */
    private const SITE_VERSION_MOBILE = 'mobile';

    /** Выполнена ли инициализация сайта */
    private static $isRunning = false;

    /**
     * Массив анонимных функций, выполняемых шаблоном
     * ПЕРЕД получением контента блока страницы.
     */
    private static $onBeforePageBlockLoaded = [];

    /**
     * Массив анонимных функций, выполняемых шаблоном
     * ПОСЛЕ получения контента блока страницы.
     */
    private static $onPageBlockLoaded = [];

    /** Свойства (поля) запрашиваемой страницы */
    private static $pageFields = [];

    /** Доп. параметры запрашиваемой страницы */
    private static $pageProperties;

    /** "Хлебные крошки" */
    private static $breadcrumbs = [];

    /** Версия сайта: [[self::SITE_VERSION_DESKTOP]] или [[self::SITE_VERSION_MOBILE]] */
    private static $siteVersion = self::SITE_VERSION_DESKTOP;

    /** Smarty object */
    private static $Smarty;

    public const PAGE_IDENT_MODULES = 'modules';

    private static $Triggers = false;

    /**
     * Проверка существования в БД таблицы с настройками сайта.
     */
    public static function checkDbIsEmpty(): void
    {
        if (!DBCommand::getTableIsExists(SiteOptions::DB_TABLE)) {
            exit('Database is empty');
        }
    }

    /**
     * Инициализация сайта.
     *
     * @param  array  $features  Ассоциативный массив со списком компонентов,
     *                           которые будут задействованы, если не задано обратное:
     *                           ['breadcrumbs' => true, 'mobile' => true, 'smarty' => true].
     */
    public static function run(array $features = []): void
    {
        if (self::$isRunning === true) {
            return;
        }

        SiteOptions::load();

        if ($features['breadcrumbs'] ?? true) {
            self::$breadcrumbs[] = DBCommand::select([
                'select' => [['name', 'url' => 'full_path', 'title']],
                'from'   => CPageMeta::getDBTable(),
                'where'  => 'id = ' . Cfg::PAGE_ID_FRONT
            ], DBCommand::OUTPUT_FIRST_ROW);
        }

        if ($features['mobile'] ?? true) {
            self::determineSiteVersion();
        }

        if ($features['smarty'] ?? true) {
            self::runSmarty();
        }

        self::runTrigger('applicationRunTrigger');

        self::$isRunning = true;
    }

    /**
     * Определение версии сайта, которую надо показать пользователю, на основе:
     *  - переменной HTTP-запроса;
     *  - наличия префикса имени хоста;
     *  - ранее сохранённого значения;
     *  - принадлежности пользовательского устройства к мобильным девайсам.
     * У переменной HTTP-запроса самый высокий приоритет.
     * Если она не соответствует имени хоста, выполняется перенаправление.
     */
    private static function determineSiteVersion(): void
    {
        // определение версии сайта выключено
        if (Cfg::MOBILE_VERSION_IS_ON !== true) {
            return;
        }

        // получение версии сайта из переменной HTTP-запроса
        self::$siteVersion = Request::getVar(self::SITE_VERSION_REQUEST_VAR_NAME, 'string');
        // содержит ли имя хоста префикс мобильной версии
        $domain = Request::getServerName(false);
        $isMobileDomain = strpos($domain, Cfg::MOBILE_DOMAIN_PREFIX) === 0
            || strpos($domain, '.' . Cfg::MOBILE_DOMAIN_PREFIX);

        if (self::$siteVersion === self::SITE_VERSION_DESKTOP) {
            if ($isMobileDomain) {
                Response::redirect( self::getDesktopUrl(true) );
            }
            self::saveSiteVersion();
        } elseif (self::$siteVersion === self::SITE_VERSION_MOBILE) {
            if (!$isMobileDomain) {
                Response::redirect( self::getMobileUrl(true) );
            }
            self::saveSiteVersion();
        } elseif ($isMobileDomain) {
            self::$siteVersion = self::SITE_VERSION_MOBILE;
        } else {
            // нужная версия ранее не была сохранена
            if ((self::$siteVersion = self::getSavedSiteVersion()) === null) {
                self::$siteVersion = Request::getIsMobileDevice()
                    ? self::SITE_VERSION_MOBILE
                    : self::SITE_VERSION_DESKTOP;
                self::saveSiteVersion();
                // сохранённая версия не равна [[self::SITE_VERSION_MOBILE]]
            } elseif (self::$siteVersion !== self::SITE_VERSION_MOBILE) {
                self::$siteVersion = self::SITE_VERSION_DESKTOP;
            }
            if (self::$siteVersion === self::SITE_VERSION_MOBILE) {
                Response::redirect(self::getMobileUrl(true));
            }
        }
    }

    /**
     * Сохранение обозначения определённой по некоторым признакам версии сайта.
     */
    private static function saveSiteVersion(): void
    {
        Cookies::add([
            'name' => self::SITE_VERSION_COOKIE_NAME,
            'value' => self::$siteVersion,
            'expire' => Time::toStamp() + Time::MONTH
        ]);
    }

    /**
     * Получение ранее сохранённого обозначения версии сайта
     *
     * @return string|null
     */
    private static function getSavedSiteVersion(): ?string
    {
        return Cookies::getValue(self::SITE_VERSION_COOKIE_NAME);
    }

    /**
     * Проверка, активна ли версия сайта для настольных устройств
     *
     * @return bool
     */
    public static function isDesktopSite(): bool
    {
        return self::$siteVersion === self::SITE_VERSION_DESKTOP;
    }

    /**
     * Проверка, активна ли версия сайта для мобильных устройств
     *
     * @return bool
     */
    public static function isMobileSite(): bool
    {
        return self::$siteVersion === self::SITE_VERSION_MOBILE;
    }

    /**
     * МЕТОД ВРЕМЕННО ОСТАВЛЕН ТОЛЬКО ДЛЯ ОБРАТНОЙ СОВМЕСТИМОСТИ.
     * @return bool
     */
    public static function isMobileVersion(): bool
    {
        return self::isMobileSite();
    }

    /**
     * Получение URL заданной альтернативной версии текущей страницы.
     *
     * @param   string  $toSiteVersion  Обозначение версии сайта
     * @param   bool    $withScheme     Надо ли указать протокол
     * @param   bool    $withToggle     Добавить ли GET-параметр для переключения версии
     * @return  string
     */
    private static function getAlternateUrl(
        string $toSiteVersion,
        bool $withScheme,
        bool $withToggle
    ): string {
        $host = Request::getServerName(false);

        $prefixInTheStart = strpos($host, Cfg::MOBILE_DOMAIN_PREFIX) === 0;
        $prefixInTheMiddle = $prefixInTheStart || strpos($host, '.' . Cfg::MOBILE_DOMAIN_PREFIX);

        if ($toSiteVersion === self::SITE_VERSION_DESKTOP) {
            if ($prefixInTheStart) {
                $host = substr($host, strlen(Cfg::MOBILE_DOMAIN_PREFIX));
            } elseif ($prefixInTheMiddle) {
                $host = str_replace(Cfg::MOBILE_DOMAIN_PREFIX, '', $host);
            }
        } elseif (
            !$prefixInTheStart
            && !$prefixInTheMiddle
            && $toSiteVersion === self::SITE_VERSION_MOBILE
        ) {
            $domains = explode('.', $host);
            $mainDomainIdx = count($domains) - Cfg::MAIN_DOMAIN_LEVEL;
            if (!isset($domains[ $mainDomainIdx ])) {
                $mainDomainIdx = 0;
            }
            $domains[ $mainDomainIdx ] = Cfg::MOBILE_DOMAIN_PREFIX . $domains[ $mainDomainIdx ];

            $host = implode('.', $domains);
        }

        $host = '//' . $host;
        if ($withScheme) {
            $host = (Request::isSecureConnection() ? 'https:' : 'http:') . $host;
        }

        return $host . Request::getRelativeURL() .
            ($withToggle
             ? Request::getQueryString([self::SITE_VERSION_REQUEST_VAR_NAME => $toSiteVersion])
             : Request::getQueryString([], [self::SITE_VERSION_REQUEST_VAR_NAME]));
    }

    /**
     * Получение URL версии текущей страницы для настольных устройств
     *
     * @param   bool  $withScheme  Надо ли указать протокол
     * @param   bool  $withToggle  Добавить ли GET-параметр для переключения версии
     * @return  string
     */
    public static function getDesktopUrl(bool $withScheme = false, bool $withToggle = false): string
    {
        return self::getAlternateUrl(self::SITE_VERSION_DESKTOP, $withScheme, $withToggle);
    }

    /**
     * Получение URL версии текущей страницы для мобильных устройств
     *
     * @param   bool  $withScheme  Надо ли указать протокол
     * @param   bool  $withToggle  Добавить ли GET-параметр для переключения версии
     * @return  string
     */
    public static function getMobileUrl(bool $withScheme = false, bool $withToggle = false): string
    {
        return self::getAlternateUrl(self::SITE_VERSION_MOBILE, $withScheme, $withToggle);
    }

    /**
     * Добавление страницы в список "хлебных крошек".
     *
     * @param  string  $name   Название страницы
     * @param  string  $url    URL страницы
     */
    public static function addBreadcrumbs(string $name, string $url = '#'): void
    {
        self::$breadcrumbs[] = ['name' => $name, 'url' => $url];
    }

    /**
     * Переопределение последнего елемента "хлебных крошек".
     *
     * @param  string  $name   Название страницы
     * @param  string  $url    URL страницы
     * @param  string  $title  Заголовок страницы
     */
    public static function deleteLastBreadcrumbs()
    {
        return array_pop(self::$breadcrumbs);
    }

    /**
     * Получение всего списка "хлебных крошек".
     *
     * @return array
     */
    public static function getBreadcrumbs(): array
    {
        return self::$breadcrumbs;
    }

    /**
     * Очистка списка "хлебных крошек".
     */
    public static function deleteBreadcrumbs(): void
    {
        self::$breadcrumbs = [];
    }

    /**
     * Определение запрашиваемой страницы сайта.
     */
    public static function detectPage(): void
    {
        self::$pageFields = ['id' => Cfg::PAGE_ID_FRONT];

        if (
            SiteOptions::get('service_mode_is_on')
            && !User::isAdmin()
            && !Request::parseUri('#^' . Cfg::URL_ADMIN_PANEL . '#', [])
        ) {
            $ServiceFilePath = Cfg::DIR_VIEW . 'special' . Cfg::DS . 'service_mode_page.php';
            if (FsFile::isExists($ServiceFilePath)) {
                require_once $ServiceFilePath;
            } else {
                echo SiteOptions::get('service_mode_message');
            }
            exit();
        }

        $url = Request::getRelativeURL();
        if ($url === '/') {
            return;
        }

        $levels = explode('/', $url);
        unset($levels[0]);

        $dbTable = CPageMeta::getDBTable();

        foreach ($levels as $key => $ident) {
            if (empty($ident)) {
                continue;
            }

            $pageLvl = DBCommand::select([
                'select' => [['id', 'name', 'url' => 'full_path', 'title', 'module']],
                'from'   => $dbTable,
                'where'  => 'parent = ' . DBCommand::qV( self::$pageFields['id'] )
                    . ' AND ident = ' . DBCommand::qV($ident)
            ], DBCommand::OUTPUT_FIRST_ROW);

            if (empty($pageLvl)) { // Страница не найдена
                self::$pageFields['id'] = 0;
                return;
            }

            self::$pageFields['id'] = $pageLvl['id'];
            self::addBreadcrumbs($pageLvl['name'], $pageLvl['url']);

            if (!empty($pageLvl['module']) && $pageLvl['module'] !== 'html') {
                $TriggerOutput = self::runTrigger('detectPageTrigger', ['module' => $pageLvl['module']]);
                foreach ($TriggerOutput as $TOut) {
                    if (!empty($TOut)) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * Отображение страницы сайта.
     *
     * @param  ?string  $url  URL запрашиваемой страницы относительно корня
     */
    public static function showPage(?string $url = null): void
    {
        if ($url) {
            $where = 'full_path = ' . DBCommand::qV($url);
        } elseif (!empty(self::$pageFields['id'])) {
            $where = 'id = ' . DBCommand::qV(self::$pageFields['id']);
        } else {
            self::notFound($url === Cfg::URL_404);
        }

        self::$pageFields = DBCommand::select([
            'from'  => CPageMeta::getDBTable(),
            'where' => $where
        ], DBCommand::OUTPUT_FIRST_ROW);

        $accessDenied = true;

        if (empty(self::$pageFields)) {

            self::notFound($url === Cfg::URL_404);

        } elseif (User::isAdmin()) {

            $accessDenied = false;

        } elseif (self::$pageFields['is_hidden']) {

            self::notFound($url === Cfg::URL_404);

        } elseif (self::$pageFields['is_public']) {

            $accessDenied = false;

        } elseif ($grpIdsStr = User::getEntity()->getPrivateExtraData('grp_ids_str')) {

            $accessDenied = !DBCommand::select([
                'select' => '1',
                'from'   => CPageMeta::getDBTablePermissions(),
                'where'  => 'page_id = ' . DBCommand::qV(self::$pageFields['id'])
                    . ' AND group_id IN (' . $grpIdsStr . ')'
                    . ' AND MOD(statuses, ' . CPageMeta::PERMS['reed'] . ') = 0'
            ], DBCommand::OUTPUT_FIRST_CELL);

        }

        if ($accessDenied) {
            $iCanLogin = $url !== Cfg::URL_ACCOUNT_LOGIN && $url !== Cfg::URL_ACCOUNT_LOGIN_ADMIN;
            self::accessDenied($url === Cfg::URL_403, $iCanLogin);
        }

        if (self::$pageFields['direct_link']) {
            Response::redirect(self::$pageFields['direct_link']);
        }

        $Module = new Module();
        $Module->show(
            'main',
            Request::getVar('action', 'string') === 'print'
                ? 'tpl_plain'
                : 'tpl_' . self::$pageFields['main_template'],
            ''
        );
    }

    /**
     * Проверка безопасности шаблона запрашиваемой страницы.
     *
     * @see Cfg::SAFE_TEMPLATE
     */
    public static function checkTemplateSafety(): void
    {
        if (self::$pageFields['main_template'] !== Cfg::SAFE_TEMPLATE) {
            throw new InternalEx('Page template is not secure');
        }
    }

    /**
     * Получение значения свойства запрашиваемой страницы.
     *
     * @param   string  $ident  Имя свойства
     * @return  string
     */
    public static function getPageInfo(string $ident): string
    {
        return self::$pageFields[ $ident ] ?? '';
    }

    /**
     * Получение значения доп. параметра, который можно установить для страницы.
     *
     * @param   string  $name  Имя свойства
     * @return  mixed
     */
    public static function getPageProperty(string $name)
    {
        if (self::$pageProperties === null) {
            self::$pageProperties = new Properties(self::$pageFields['props']);
        }

        return self::$pageProperties->$name;
    }

    /**
     * Получение HTML-кода запрашиваемой страницы и кнопки для перехода к редактированию страницы.
     *
     * @return string
     */
    public static function getPageTextContent(): string
    {
        $textContent = Html::entityEditButtons(
            'CPage',
            [Action::UPDATE],
            [
                'id' => self::$pageFields['id'],
                'userGroups' => CPageMeta::getPermissions(Action::UPDATE)
            ]
        );
        if (self::$pageFields['content_mobile'] && self::isMobileSite()) {
            $textContent .= Html::strip(Html::dSC(self::$pageFields['content_mobile']));
        } elseif (self::$pageFields['content']) {
            $textContent .= Html::strip(Html::dSC(self::$pageFields['content']));
        }

        return $textContent;
    }

    /**
     * Подключение содержимого модуля (файла-шаблона).
     *
     * @param   string  $module  Идентификатор модуля
     * @param   string  $dir     Директория контроллера
     * @param   string  $file    Файл контроллера
     * @param   mixed   $props   Параметры, передаваемые модулю
     * @return  string
     */
    private static function getModuleContent(string $dir = '', string $file = '', string $module = '', $props = [] ): string
    {
        ob_start();

        $Module = new Module();
        $Module->setProps($props);
        $Module->show($dir, $file, $module);

        return ob_get_clean();
    }

    /**
     * Получение содержимого блока.
     *
     * @param   string  $ident  Идентификатор запрашиваемого модуля
     * @param   mixed   $props  Передаваемые модулю параметры
     * @return  string
     */
    public static function getBlock(string $ident, $props = null): string
    {
        $block = BlockManager::getBlockData($ident);
        return self::getModuleContent(
            '',
            '',
            $block['ident'],
            $props
        );
    }

    /**
     * Получение содержимого виджета блока.
     */
    public static function getWidget(string $dir, string $file, string $module = '', array $props = []): string
    {
        $props['is_widget'] = true;
        return self::getModuleContent($dir, $file, $module, $props);
    }

    /**
     * Получение содержимого текстового блока.
     *
     * @param   string  $ident        Идентификатор запрашиваемого блока
     * @param   bool    $withButtons  Добавить в начало код кнопок редактирования
     * @return  string
     */
    public static function getTextBlock(string $ident, bool $withButtons = true): string
    {
        return TextBlockManager::getContent($ident, true, $withButtons);
    }

    /**
     * Получение содержимого страницы сайта.
     *
     * @return string
     */
    public static function getPageBlock(): string
    {
        if (empty(self::$pageFields)) {
            throw new InternalEx('Site page is not defined');
        }

        if (self::$pageFields['controller_dir'] && self::$pageFields['controller_file']) {
            return self::getModuleContent(
                self::$pageFields['controller_dir'],
                self::$pageFields['controller_file'],
                '',
                self::$pageFields['props']
            );
        }

        if (self::$pageFields['module'] === 'html') {
            return self::getPageTextContent();
        }

        return self::getBlock(self::$pageFields['module'], self::$pageFields['props']);
    }

    /**
     * Отображение страницы по умолчанию для некоторых специальных случаев
     *
     * @param  int  $code  Код состояния HTTP
     */
    private static function showDefaultErrorPage(int $code): void
    {
        $supportedCodes = [
            Response::STATUS_FORBIDDEN => true,
            Response::STATUS_NOT_FOUND => true,
            Response::STATUS_INTERNAL_SERVER_ERROR => true,
            Response::STATUS_SERVICE_UNAVAILABLE => true
        ];
        if (!isset($supportedCodes[$code])) {
            $code = Response::STATUS_INTERNAL_SERVER_ERROR;
        }

        $defaultPagePath = Cfg::DIR_VIEW . 'error_pages' . Cfg::DS . $code . '.html';
        if (FsFile::isExists($defaultPagePath)) {
            readfile($defaultPagePath);
        } else {
            echo $code;
        }

        exit;
    }

    /**
     * Отображение страницы 403. Гость увидит страницу авторизации (если прав хватит).
     * На случай отсутствия прав на просмотр страницы с 'full_path' == URL_403
     * предусмотрена возможность отображения страницы по умолчанию.
     *
     * @param  bool  $default    Будет ли отображена страница по умолчанию?
     * @param  bool  $iCanLogin  Есть ли права на просмотр страницы авторизации?
     */
    public static function accessDenied(bool $default = false, bool $iCanLogin = true): void
    {
        self::deleteBreadcrumbs();

        if (!$iCanLogin || User::isLoggedIn()) {
            Response::setStatusCode(Response::STATUS_FORBIDDEN);
            if ($default) {
                self::showDefaultErrorPage(Response::STATUS_FORBIDDEN);
            }
            $redirectTo = Cfg::URL_403;
        } else {
            Response::setStatusCode(Response::STATUS_UNAUTHORIZED);
            Session::set('access_denied', 'request_uri', Request::getUri());
            if (self::getPageInfo('is_system')) {
                $redirectTo = Cfg::URL_ACCOUNT_LOGIN_ADMIN;
            } else {
                $redirectTo = Cfg::URL_ACCOUNT_LOGIN;
            }
        }

        self::showPage($redirectTo);

        exit;
    }

    /**
     * Отображение страницы 404.
     * На случай отсутствия в БД страницы с 'full_path' == URL_404
     * предусмотрена возможность отображения страницы по умолчанию.
     *
     * @param  bool  $default  Будет ли отображена страница по умолчанию?
     */
    public static function notFound(bool $default = false): void
    {
        if (Cfg::GUARD_IS_ON) {
            Guard::logEvent(
                Guard::CODES['not_found'],
                User::login(),
                Request::getUri()
            );
        }

        Response::setStatusCode(Response::STATUS_NOT_FOUND);

        self::deleteBreadcrumbs();

        if ($default) {
            self::showDefaultErrorPage(Response::STATUS_NOT_FOUND);
        }

        self::showPage(Cfg::URL_404);

        exit;
    }

    /**
     * Установка названия страницы и некоторых её SEO-параметров.
     */
    public static function importPageAttributes(): void
    {
	    RTP::setPageName(self::$pageFields['h1'] ?: self::$pageFields['name']);
	    RTP::setPageTitle(self::$pageFields['title'] ?: self::$pageFields['name']);
        RTP::setKeywords(self::$pageFields['keywords'] ?: SiteOptions::get('site_keywords'));
        RTP::setDescription(self::$pageFields['description'] ?: SiteOptions::get('site_description'));
	    RTP::setNoindex((bool)self::$pageFields['noindex']);

        if (Cfg::MOBILE_VERSION_IS_ON === true) {
            if (self::isMobileSite()) {
                RTP::set('canonicalHref', self::getDesktopUrl(true));
            } else {
                RTP::set('alternateHrefMobile', self::getMobileUrl(true));
            }
        }
    }

    /**
     * Регистрация функции, которая должна выполниться
     * ПЕРЕД получением контента блока страницы.
     *
     * @param  callable  $callback
     */
    public static function onBeforePageBlockLoaded(callable $callback): void
    {
        self::$onBeforePageBlockLoaded[] = $callback;
    }

    /**
     * Регистрация функции, которая должна выполниться
     * ПОСЛЕ получения контента блока страницы.
     *
     * @param  callable  $callback
     */
    public static function onPageBlockLoaded(callable $callback): void
    {
        self::$onPageBlockLoaded[] = $callback;
    }

    /**
     * Выполнение функций, назначенных на событие
     * ПЕРЕД получением контента блока страницы.
     */
    public static function prePageBlockLoad(): void
    {
        foreach (self::$onBeforePageBlockLoaded as $callback) {
            $callback();
        }
    }

    /**
     * Выполнение функций, назначенных на событие
     * ПОСЛЕ получения контента блока страницы.
     */
    public static function postPageBlockLoad(): void
    {
        foreach (self::$onPageBlockLoaded as $callback) {
            $callback();
        }

        Response::sendCookies();
    }

    /**
     * Передача контента блока страницы сайта в Smarty-переменную [[$blockPage]].
     */
    public static function loadPageBlock(): void
    {
        self::prePageBlockLoad();

        $pageBlockData = self::getPageBlock();

        if (
        	class_exists('PhotoGallery')
	        && !self::getPageInfo('is_system')
	        && $content = PhotoGallery::applyGalleryOverrides($pageBlockData)
        ) {
	        $pageBlockData = $content;
        }

        self::assign('blockPage', $pageBlockData);

        self::postPageBlockLoad();
    }

    /**
     * Проверка готовности Smarty.
     *
     * @return bool
     */
    public static function smartyIsReady(): bool
    {
        return self::$Smarty instanceof Smarty;
    }

    /**
     * Инициализация Smarty.
     */
    private static function runSmarty(): void
    {
        self::$Smarty = new Smarty();
        self::$Smarty->setTemplateDir(Cfg::SMARTY_TEMPLATE_DIR)
                     ->setCompileDir(Cfg::SMARTY_COMPILE_DIR)
                     ->setCacheDir(Cfg::SMARTY_CACHE_DIR)
                     ->setCompileCheck(Smarty::COMPILECHECK_ON);
        self::$Smarty->setLeftDelimiter(Cfg::SMARTY_LEFT_DELIMITER);
        self::$Smarty->setRightDelimiter(Cfg::SMARTY_RIGHT_DELIMITER);
        self::$Smarty->setErrorReporting(~E_NOTICE);
        self::$Smarty->addPluginsDir(Cfg::SMARTY_PLUGINS_DIR);
    }

    /**
     * Присвоение значения переменной Smarty.
     *
     * @param  array|string  $tplVar   Имя переменной или массив ['name1' => 'value1']
     * @param  mixed         $value    Значение переменной
     * @param  bool          $nocache  Если TRUE, никакой вывод этой переменной кэшироваться не будет
     */
    public static function assign($tplVar, $value = null, bool $nocache = false): void
    {
        if (!self::smartyIsReady()) {
            self::runSmarty();
        }
        self::$Smarty->assign($tplVar, $value, $nocache);
    }

    /**
     * Рендеринг шаблона Smarty.
     * В зависимости от значения $action содержимое шаблона
     * либо возвращается в виде строки, либо выводится.
     * Путь к шаблону указывается от SMARTY_TEMPLATE_DIR и может быть представлен в виде строки
     * или массива из имён директорий и файла ( ['path', 'to', 'file'] ).
     *
     * @param   string  $action     Необходимое действие: 'fetch'|'display'
     * @param   string  $module     Имя модуля, к которому относится шаблон
     * @param   string  $template   Имя файла шаблона без расширения
     * @param   mixed   $cacheId    ID кэширования данного шаблона (cache id to be used with this template)
     * @param   mixed   $compileId  ID компиляции данного шаблона (compile id to be used with this template)
     * @param   ?object  $parent     Родительский объект Smarty (next higher level of Smarty variables)
     * @return  string
     */
    private static function getSmartyTemplate(
        string $action,
        string $module,
        string $template,
        $cacheId = null,
        $compileId = null,
        ?object $parent = null
    ): string {
        if (!self::smartyIsReady()) {
            self::runSmarty();
        }

        $template = "file:[{$module}]{$template}" . Cfg::SMARTY_TEMPLATE_EXT;

        if ($action === 'fetch') {
            return self::$Smarty->fetch($template, $cacheId, $compileId, $parent);
        }

        self::$Smarty->display($template, $cacheId, $compileId, $parent);
        return '';
    }

    /**
     * Получение шаблона Smarty.
     *
     * @see Application::getSmartyTemplate()
     */
    public static function getContent(
        string $module,
        string $template,
        $cacheId = null,
        $compileId = null,
        object $parent = null
    ): string {
        return self::getSmartyTemplate('fetch', $module, $template, $cacheId, $compileId, $parent);
    }

    /**
     * Отображение шаблона Smarty.
     *
     * @see     Application::getSmartyTemplate()
     * @param   string
     * @param   string
     * @param   mixed
     * @param   mixed
     * @param   object|null
     */
    public static function showContent(
        string $module,
        string $template,
        $cacheId = null,
        $compileId = null,
        object $parent = null
    ): void {
        self::getSmartyTemplate('display', $module, $template, $cacheId, $compileId, $parent);
    }

    public static function getMainTemplates() {
        return DBCommand::select([
            'from' => Cfg::DB_TBL_PREFIX . self::DB_TABLE_MAIN_TEMPLATES,
            'where' => 'is_system=0',
            'order' => ['posit' => DBQueryBuilder::ASC]
        ]);
    }

    public static function plugRegisteredWidgets()
    {
        if (empty(self::$pageFields['main_template'])) {
            return false;
        }

        $blocks = DBCommand::select([
            'select' => 'm.ident',
            'from' => CBlockMeta::getDBTable() . ' AS m',
            'join' => 'LEFT JOIN ' . CBlockMeta::getDBTableModulesToTemplates() . ' AS mtt ON m.ident=mtt.module_ident',
            'where' => 'm.is_widget = 1 AND mtt.template_ident = ' . DBCommand::qV(self::$pageFields['main_template'])
        ], DBCommand::OUTPUT_FIRST_COLUMN);

        foreach ($blocks as $block) {
            self::assign($block . 'Widget', self::getWidget('', '', $block));
        }

        return false;
    }

    /**
     * Вызов статической функции с именем $function в каждом зарегистрированном модуле.
     * Полученные данные собираются в единый массив.
     *
     * @param   string  $function  Имя функции в классе модуля
     * @param   array   $Data      Передаваемые данные в функцию модуля
     * @return  array
     */
    public static function runTrigger(string $function, array $Data = []): array
    {
        if (!isset(self::$Triggers[$function])) {
            self::$Triggers[$function] = DBCommand::select([
                'from' => CBlockMeta::getDBTableTriggers(),
                'where' => 'function =' . DBCommand::qV($function)
            ]);
        }

        $output = [];

        foreach (self::$Triggers[$function] as $trigger) {
            if (!empty($trigger['class'])) {
                $class = $trigger['class'];
            } else {
                $class = $trigger['module'] . 'Manager';
            }

            $class_function = "{$class}::{$function}";

            if (class_exists($class) && is_callable($class_function)) {
                $output[$trigger['module'] . 'Output'] = $class_function($Data);
            }
        }

        return $output;
    }

    /**
     * Регистрация триггера
     *
     * @param  string  $module    Идентификатор модуля
     * @param  string  $class     Имя класса
     * @param  string  $function  Имя функции
     * @return int
     */
    public static function insertTrigger(string $module, string $class, string $function): int
    {
        return DBCommand::insert(
            CBlockMeta::getDBTableTriggers(),
            [
                'module' => $module,
                'class' => $class,
                'function' => $function
            ]
        );
    }

    public static function getBackLink() {
        $breadcrumbs = self::getBreadcrumbs();
        $count = count($breadcrumbs);

        if ($count < 2) {
            return '';
        }

        return $breadcrumbs[$count-2]['url'];
    }

    public static function setPageInfo( $FieldName, $FieldValue )
    {
        self::$pageFields[ $FieldName ] = $FieldValue;
    }
}
