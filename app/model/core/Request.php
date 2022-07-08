<?php
/**
 * Статичный класс Request.
 *
 * Класс для обработки HTTP запросов.
 *
 * @author Dmitriy Lunin
 */
class Request
{
    /**
     * @see Request::parseUri()
     * @var array
     */
    protected static $foundInUri = [];

    /**
     * @see Request::getUserBrowser()
     * @var array
     */
    protected static $client;

    /**
     * @see Request::isIE()
     * @var bool
     */
    protected static $isIEbyUA;

    /**
     * @see Request::getIsMobileDevice()
     * @var bool
     */
    protected static $isMobileDevice;

    /**
     * Получение запрошенного пользователем URI.
     *
     * @return string
     */
    public static function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /**
     * Получение относительного пути к текущей странице, без GET-параметров
     *
     * @return  string
     */
    public static function getRelativeURL(): string
    {
        $url = self::getUri();

        if (($cropLen = strpos($url, '?')) !== false) {
            $url = substr($url, 0, $cropLen);
        }

        return $url;
    }

    /**
     * Получение строки запросов, если она есть, с помощью которой была получена страница.
     * Добавление/изменение параметров, если они были заданы.
     * Имена параметров должны быть строками, в качестве значений допускаются
     * строки (включая пустые) и одноуровневые массивы.
     *
     * @param   array  $addParam  Параметры, которые надо добавить/изменить
     * @param   array  $delParam  Параметры, которые надо удалить
     * @return  string
     */
    public static function getQueryString(array $addParam = [], array $delParam = []): string
    {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';

        if ($addParam || $delParam) {
            if ($queryString) {
                parse_str($queryString, $currentParameters);
                foreach ($delParam as $p) {
                    unset($currentParameters[$p]);
                }
                $addParam = array_merge($currentParameters, $addParam);
            }

            $queryString = '';
            $separator = ini_get('arg_separator.output');
            foreach ($addParam as $param => $value) {
                if (!is_string($param)) {
                    continue;
                }
                if ($queryString) {
                    $queryString .= $separator;
                }
                $param = urlencode($param);
                $queryString .= $param;
                if (is_string($value) && $value !== '') { // foo=bar&bar=foo
                    $queryString .= '=' . urlencode($value);
                } elseif (is_array($value)) { // foo[]=bar&bar[foo]=biz
                    $notFirstKey = false;
                    foreach ($value as $key => $val) {
                        if (!is_string($val) || $val === '') {
                            continue;
                        }
                        if ($notFirstKey) {
                            $queryString .= "{$separator}{$param}";
                        } else {
                            $notFirstKey = true;
                        }
                        // urlencode('[') === %5B
                        // urlencode(']') === %5D
                        if (is_numeric($key)) {
                            $queryString .= '%5B%5D=' . urlencode($val);
                        } else {
                            $queryString .= "%5B{$key}%5D=" . urlencode($val);
                        }
                    }
                }
            }

            $queryString = str_replace('%2F', '/', $queryString);
        }

        return $queryString ? "?{$queryString}" : '';
    }

    /**
     * Вычленение из [REQUEST_URI] параметров на основе регулярного выражения.
     *
     * @param   string  $pattern  Искомый шаблон
     * @param   array   $keys     Имена параметров
     * @return  bool    FALSE, если шаблон не был найден
     */
    public static function parseUri(string $pattern, array $keys): bool
    {
        if (!preg_match($pattern, self::getUri(), $matches)) {
            return false;
        }

        unset($matches[0]);
        foreach ($keys as $i => $name) {
            if (isset($matches[ $i + 1 ])) {
                self::$foundInUri[ $name ] = $matches[ $i + 1 ];
            }
        }

        return true;
    }

    /**
     * Получение значения параметра, ранее найденного [[self::parseUri()]].
     *
     * @param   string  $name  Имя параметра
     * @return  string
     */
    public static function getFromUri(string $name): string
    {
        return self::$foundInUri[ $name ] ?? '';
    }

    /**
     * Поиск в [[$_REQUEST]] заданного параметра указанного типа.
     * В случае неудачи возвращается значение по умолчанию.
     *
     * @param   string  $name     Имя параметра
     * @param   string  $type     Требуемый тип
     * @param   mixed   $default  Значение по умолчанию
     * @param   bool    $filled   Должен ли параметр быть !empty ?
     * @return  mixed
     */
    public static function getVar(string $name, string $type, $default = null, bool $filled = null)
    {
        if (isset($_REQUEST[ $name ])) {
            $req = $_REQUEST[ $name ];
            return verifyValue($req, $type, $filled) ? $req : $default;
        }

        return $default;
    }

    /**
     * Получение IP адреса сервера и HTTP-порта.
     *
     * @return string
     */
    public static function getServerIP(): string
    {
        return self::getServerVar('SERVER_ADDR') . ':' . self::getServerVar('SERVER_PORT');
    }

    /**
     * Выполнен ли запрос через защищённое соединение (HTTPS)
     *
     * @return  bool
     */
    public static function isSecureConnection(): bool
    {
        return (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') !== 0)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0);
    }

    /**
     * Получение имени хоста из запрошенного URI (без слэша в конце).
     *
     * @param   bool  $withScheme  Добавить ли к имени хоста схему
     * @return  string
     */
    public static function getServerName(bool $withScheme = true): string
    {
        $hostInfo = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        if ($withScheme && $hostInfo) {
            $hostInfo = self::getProtocol() . '://' . $hostInfo;
        }
        return $hostInfo;
    }

    /**
     * Получение значения переменной настройки сервера.
     * Если сервер нужной информации не предоставил, используются переменные окружения.
     *
     * @param   string  $name  Имя переменной
     * @return  mixed
     */
    public static function getServerVar(string $name)
    {
        return $_SERVER[ $name ] ?? getenv($name);
    }

    /**
     * Получение протокола
     *
     * @return string
     */
    public static function getProtocol(): string
    {
        return self::isSecureConnection() ? 'https' : 'http';
    }

    /**
     * Получение оригинального хоста без регионального поддомна и поддомена мобильной версии
     * barnaul.web-ae.ru     ->  web-ae.ru
     * m.web-ae.ru           ->  web-ae.ru
     * barnaul.m.web-ae.ru   ->  web-ae.ru
     *
     * @param   bool    $withScheme  Надо ли указать протокол
     * @return  string
     */
    public static function getOriginalHost(bool $withScheme = true): string
    {
        $Host = self::getServerName(false);

        $MainDomainLevel = Cfg::MAIN_DOMAIN_LEVEL ?? 2;

        $domains = explode('.', $Host);
        $mainDomainIdx = count($domains) - $MainDomainLevel;

        for ($i = 0; $i < $mainDomainIdx; $i++) {
            unset($domains[$i]);
        }

        $OriginalHost = implode('.', $domains);

        if ($withScheme && $OriginalHost) {
            $OriginalHost = self::getProtocol() . '://' . $OriginalHost;
        }

        return $OriginalHost;
    }

    /**
     * Получение IP адреса посетителя сайта. Если используется прозрачный прокси-сервер,
     * можно попытаться определить реальный IP.
     *
     * @param   bool  $checkProxy  Попробовать получить IP за прокси. Не надёжно!
     * @return  string
     */
    public static function getUserIP(bool $checkProxy = false): string
    {
        if ($checkProxy) {
            if (
                ($ip = self::getServerVar('HTTP_CLIENT_IP'))
                && ($ip = filter_var($ip, FILTER_VALIDATE_IP))
            ) {
                return $ip;
            }

            if (
                ($ip = self::getServerVar('HTTP_X_FORWARDED_FOR'))
                && ($ip = filter_var($ip, FILTER_VALIDATE_IP))
            ) {
                return $ip;
            }
        }

        return self::getServerVar('REMOTE_ADDR');
    }

    /**
     * Получение значения HTTP-заголовка "User Agent".
     *
     * @return string
     */
    public static function getUserAgent(): string
    {
        return self::getServerVar('HTTP_USER_AGENT');
    }

    /**
     * Браузером пользователя является Internet Explorer?
     * Определение выполняется на основе заголовка "User Agent".
     *
     * @return  bool
     */
    public static function isIE(): bool
    {
        if (self::$isIEbyUA === null) {
            $userAgent = self::getUserAgent();
            self::$isIEbyUA = strpos($userAgent, 'MSIE') !== false
                || strpos($userAgent, 'Trident') !== false;
        }

        return self::$isIEbyUA;
    }

    /**
     * Определение браузера на основе заголовка "User Agent".
     *
     * @link  https://developer.mozilla.org/en-US/docs/Web/HTTP/Browser_detection_using_the_user_agent
     * @link  http://www.useragentstring.com/pages/useragentstring.php
     *
     * @param   string  $userAgent  User Agent для анализа.
     * @return  array   ['browser' => 'название браузера', 'version' => 'номер версии']
     */
    public static function determineBrowser(string $userAgent): array
    {
        $info = [];

        // некоторые браузеры могут маскироваться под другие, поэтому сначала пытаемся определить их
        if (preg_match('/Opera[ \/]([\d.]+)$/', $userAgent, $matches)) { // Opera в режиме маскировки
            $info['browser'] = 'Opera';
            $info['version'] = $matches[1];
            return $info;
        }

        if (preg_match('/Edge\/([\d.]+)$/', $userAgent, $matches)) { // Edge
            $info['browser'] = 'Edge';
            $info['version'] = $matches[1];
            return $info;
        }

        preg_match(
            '/(Edge|MSIE|Maxthon|Firefox|SeaMonkey|Iceweasel|Camino|K-Meleon|Chrome|Chromium|CriOS|Safari|Opera)[\/| ]([\d.]+)/',
            $userAgent,
            $matches
        );
        if (!empty($matches)) {
            $info['browser'] = $matches[1];
            $info['version'] = $matches[2];
        } elseif (strpos($userAgent, 'Trident')) { // IE 11
            $info['browser'] = 'IE';
            $info['version'] = preg_match('/rv:([\d.]+)/', $userAgent, $matches) ? $matches[1] : null;
        } else {
            return $info;
        }

        if ($info['browser'] === 'MSIE') {
            $info['browser'] = 'IE';
        } elseif ($info['browser'] === 'Chromium' || $info['browser'] === 'CriOS') {
            $info['browser'] = 'Chrome';
        } elseif (
            ($info['browser'] === 'Opera' || $info['browser'] === 'Safari')
            && preg_match('/Version\/([\d.]+)/', $userAgent, $matches)
        ) {
            $info['version'] = $matches[1];
        }

        return $info;
    }

    /**
     * Определение браузера пользователя на основе заголовка "User Agent".
     *
     * @see     Request::determineBrowser()
     * @return  array
     */
    public static function getUserBrowser(): array
    {
        if (self::$client === null) {
            if ($userAgent = self::getUserAgent()) {
                self::$client = self::determineBrowser($userAgent);
            } else {
                self::$client = [];
            }
        }

        return self::$client;
    }

    /**
     * Проверка "правильности" браузера пользователя.
     *
     * @param   array     $allowed  Массив из названий браузеров, их минимальных версий и ссылок на скачивание
     *   Пример: [
     *     'Opera' => [ 'name' => 'Opera', 'version' => 12, 'link' => 'http://ru.opera.com/download/' ],
     *   ]
     * @return  string[]  Можно ли пользоваться данным браузером, и если нет - что делать
     */
    public static function validUserBrowser(array $allowed): array
    {
        $result = ['answer' => 'bad'];
        $userBrowser = self::getUserBrowser();

        if (!empty($userBrowser)) {
            foreach ($allowed as $browser => $info) {
                if ($userBrowser['browser'] === $browser) {
                    if (version_compare($info['version'], $userBrowser['version'], '>')) {
                        $result['answer'] = 'old';
                        $result['name'] = $info['name'];
                        $result['yourVersion'] = $userBrowser['version'];
                        $result['correctVersion'] = $info['version'];
                        $result['link'] = $info['link'];
                    } else {
                        $result['answer'] = 'good';
                    }

                    break;
                }
            }
        }

        if ($result['answer'] === 'bad') {
            $result['allowed'] = $allowed;
        }

        return $result;
    }

    /**
     * Проверка принадлежности IP к списку роботов известных поисковых систем.
     * Если IP не задан, берётся адрес посетителя сайта.
     *
     * YandexBot:    https://yandex.ru/support/webmaster/robot-workings/check-yandex-robots.xml
     * Googlebot:    https://support.google.com/webmasters/answer/80553
     * Mail.RU_Bot:  https://help.mail.ru/webmaster/indexing/robots/go_robot
     * bingbot:      https://blogs.bing.com/webmaster/2012/08/31/how-to-verify-that-bingbot-is-bingbot
     * DuckDuckBot:  https://duckduckgo.com/duckduckbot
     * Yahoo! Slurp: http://www.ysearchblog.com/2007/06/05/yahoo-search-crawler-slurp-has-a-new-address-and-signature-card/
     * Baiduspider:  http://help.baidu.com/question?prod_en=master&class=Baiduspider
     *
     * @param  ?string  $ip  IP
     * @return  bool
     */
    public static function isSearchBot(?string $ip = null): bool
    {
        // некоторые роботы проверяются только по IP
        static $ipList = [
            // DuckDuckBot
            '20.191.45.212' => true,
            '23.21.227.69' => true,
            '40.88.21.235' => true,
            '50.16.241.113' => true,
            '50.16.241.114' => true,
            '50.16.241.117' => true,
            '50.16.247.234' => true,
            '52.5.190.19' => true,
            '52.204.97.54' => true,
            '54.197.234.188' => true,
            '54.208.100.253' => true,
            '54.208.102.37' => true,
            '107.21.1.8' => true,
        ];

        if ($ip === null) {
            $ip = self::getUserIP();
        }

        if (isset($ipList[ $ip ])) {
            return true;
        }

        $hostName = gethostbyaddr($ip);

        return $hostName !== false
            && $hostName !== $ip
            && gethostbyname($hostName) === $ip
            && preg_match('/\.(?:yandex\.(?:ru|com|net)|(?:google|googlebot)\.com|mail\.ru|search\.msn\.com|duckduckgo\.com|crawl\.yahoo\.net|baidu\.com)$/', $hostName);
    }

    /**
     * Является ли устройство пользователя мобильным (телефон/планшет/...)?
     *
     * @return  bool
     */
    public static function getIsMobileDevice(): bool
    {
        if (self::$isMobileDevice === null) {
            ClassLoader::loadClass('Mobile_Detect');
            $MobileDetect = new Mobile_Detect();
            self::$isMobileDevice = $MobileDetect->isMobile();
        }

        return self::$isMobileDevice;
    }
}
