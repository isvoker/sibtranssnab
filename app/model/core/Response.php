<?php
/**
 * Статичный класс Response.
 *
 * Класс для организации ответов сервера.
 *
 * @author Dmitriy Lunin
 */
class Response
{
    public const STATUS_OK = 200;
    public const STATUS_FOUND = 302;
    public const STATUS_BAD_REQUEST = 400;
    public const STATUS_UNAUTHORIZED = 401;
    public const STATUS_FORBIDDEN = 403;
    public const STATUS_NOT_FOUND = 404;
    public const STATUS_NOT_ACCEPTABLE = 406;
    public const STATUS_URI_TOO_LONG = 414;
    public const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const STATUS_INTERNAL_SERVER_ERROR = 500;
    public const STATUS_SERVICE_UNAVAILABLE = 503;

    public const STATUS_UNKNOWN_ERROR = 500;

    public const STATUSES_TEXT = [
        200 => 'OK',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        406 => 'Not Acceptable',
        414 => 'Request-URI Too Long',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable'
    ];

    /**
     * Отправка HTTP cookies.
     */
    public static function sendCookies(): void
    {
        $cookies = Cookies::getResponseCookies();
        if (empty($cookies)) {
            return;
        }

        foreach ($cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httpOnly']
            );
        }
    }

    /**
     * Установка кода состояния HTTP, отправка заголовков.
     *
     * @param  int  $statusCode  Код состояния
     */
    public static function setStatusCode(int $statusCode = self::STATUS_OK): void
    {
        switch ($statusCode) {
            case self::STATUS_FORBIDDEN:
                http_response_code(self::STATUS_FORBIDDEN);
                self::setDoNotUseCache();
                break;
            case self::STATUS_URI_TOO_LONG:
                http_response_code(self::STATUS_URI_TOO_LONG);
                self::setDoNotUseCache();
                self::setHeader('Connection', 'Close');
                break;
            default:
                http_response_code($statusCode);
                break;
        }
    }

    /**
     * Отправка HTTP-заголовка.
     *
     * @param  string  $name
     * @param  string  $value
     */
    public static function setHeader(string $name, string $value): void
    {
        header("{$name}: {$value}");
    }

    /**
     * Указание формата и способа представления данных.
     *
     * @param  string  $contentType
     * @param  string  $charset
     */
    public static function setContentType(
        string $contentType = 'text/html',
        string $charset = Cfg::CHARSET
    ): void {
        self::setHeader('Content-Type', "{$contentType}; charset={$charset}");
    }

    /**
     * Указание клиенту использовать кэш.
     *
     * @param  int  $maxAge  В течение скольких секунд кэш будет актуален
     */
    public static function setUseCache(int $maxAge): void
    {
        self::setHeader('Cache-Control', 'public, max-age=' . $maxAge);
    }

    /**
     * Указание клиенту не использовать кэш.
     */
    public static function setDoNotUseCache(): void
    {
        self::setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Завершение работы приложения с опциональным указанием причины.
     *
     * @param  int  $statusCode Код состояния HTTP
     */
    public static function close(int $statusCode): void
    {
        if (isset(self::STATUSES_TEXT[ $statusCode ])) {
            exit($statusCode . ' ' . self::STATUSES_TEXT[ $statusCode ]);
        }
        exit;
    }

    /**
     * Перенаправление клиента на заданную страницу.
     * Допускаются как относительный, так и абсолютный URL.
     *
     * @param  string  $url         Целевой URL
     * @param  int     $statusCode  Код состояния HTTP
     */
    public static function redirect(string $url, int $statusCode = self::STATUS_FOUND): void
    {
        if (strpos($url, '://') === false) {
            $url = Request::getServerName() . $url;
        }
        self::setStatusCode($statusCode);
        self::setHeader('Location', $url);
        exit('Redirect');
    }
}
