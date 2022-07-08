<?php

use Carbon\Carbon;

/**
 * Статичный класс Guard.
 *
 * Мониторинг неудачных попыток аутентификации и обращений к несуществующим страницам,
 * проверка переданного пользователем URI на аномалии.
 *
 * @author Dmitriy Lunin
 */
class Guard
{
    /** Имена таблиц БД */
    protected const DB_TABLE_LOG = Cfg::DB_TBL_PREFIX . 'guard_log';
    protected const DB_TABLE_LOCKOUTS = Cfg::DB_TBL_PREFIX . 'guard_lockouts';

    /** За FAIL_THRESHOLD нарушений в течение FAIL_PERIOD минут выдаётся бан на BANPERIOD минут */
    public const FAIL_PERIOD = 60;
    public const FAIL_THRESHOLD = 5;
    public const BANPERIOD = 30; // при систематических нарушениях вычисляется по ф-ле BANPERIOD * $banCnt^2
    /** За BANLIMIT временных банов назначается перманентный бан */
    public const BANLIMIT = 5;

    /** Максимальная длина строки с данными о событии */
    protected const DATA_MAX_LENGTH = 512;

    /** Маркеры начала и конца секции Guard'а в Access-файле */
    protected const ACCESS_FILE_MARKER_BEGIN = '### BEGIN Guard block';
    protected const ACCESS_FILE_MARKER_END = '### END Guard block';

    /** Коды событий */
    public const CODES = [
        'unauthorized' => 401,
        'forbidden'    => 403,
        'not_found'    => 404
    ];

    /** Описания кодов событий */
    public const CODES_DESCRIPTION = [
        self::CODES['unauthorized'] => 'Неудачная попытка аутентификации',
        self::CODES['forbidden']    => 'Запрещённое действие',
        self::CODES['not_found']    => 'Попытка открыть несуществующую страницу'
    ];

    /** Логины, использование которых сразу приводит к временному бану */
    protected const VERY_BAD_LOGINS = [
        'admin' => true,
        'administrator' => true
    ];

    /** Столбцы в таблицах БД */
    protected const DB_COLUMNS_LOG = [
        'id' => ['sortable' => true],
        'code' => ['sortable' => true],
        'time' => ['sortable' => true],
        'ip' => ['sortable' => true],
        'user_login' => ['sortable' => true],
        'data' => ['maxlength' => 64]
    ];
    protected const DB_COLUMNS_LOCKS = [
        'id' => ['sortable' => true],
        'active' => ['sortable' => true],
        'time_start' => ['sortable' => true],
        'time_end' => ['sortable' => true],
        'ip' => ['sortable' => true]
    ];

    /** Максимально допустимая длина запрошенного пользователем URI */
    protected static $requestUriMaxLength = 2000;

    /** Кол-во записей, соответствующих ранее выполненному SQL-запросу (без учёта LIMIT) */
    protected static $foundRowsLogs;
    protected static $foundRowsLocks;

    /**
     * Является ли заданный IP хоста одним из доверенных?
     * При $trustBots == true хосты "хороших" поисковых роботов тоже
     * считаются доверенными.
     *
     * @param   string  $ip         IP
     * @param   bool    $trustBots  "Доверять" ли поисковым роботам
     * @return  bool
     */
    protected static function ipInWhiteList(string $ip, bool $trustBots = false): bool
    {
        if ($trustBots && Request::isSearchBot($ip)) {
            return true;
        }

        if (empty(Cfg::GUARD_WHITE_LIST)) {
            return false;
        }

        $ip = filter_var($ip, FILTER_VALIDATE_IP);

        return $ip && strpos(Cfg::GUARD_WHITE_LIST, $ip) !== false;
    }

    /**
     * Логирование события и инициация бана при необходимости.
     *
     * @param  int     $code      Код события (403, 404 и т.п.)
     * @param ?string  $user      Логин пользователя, с которым связано событие
     * @param ?string  $data      Доп. информация о событии (например, URL для 404)
     * @param  bool    $forceBan  Требуется безусловный бан IP
     */
    public static function logEvent(
        int $code,
        ?string $user = null,
        ?string $data = null,
        bool $forceBan = false
    ): void {
        if (!Cfg::GUARD_IS_ON) {
            return;
        }

        $event = [
            'code' => $code,
            'ip' => Request::getUserIP()
        ];
        if (!empty($user)) {
            $event['user_login'] = $user;
        }
        if (!empty($data)) {
            $event['data'] = truncate($data, self::DATA_MAX_LENGTH);
        }

        $event = array_map(['Html', 'qSC'], $event);
        DBCommand::insert(self::DB_TABLE_LOG, $event);
        if (self::ipInWhiteList($event['ip'], $code === self::CODES['not_found'])) {
            return;
        }

        // Не пора ли банить?
        if (
            $forceBan
            || ($code === self::CODES['unauthorized']
                && isset($event['user_login'], self::VERY_BAD_LOGINS[ $event['user_login'] ]))
        ) {
            self::lockout($code, $event['ip']);
            return;
        }

        $Now = Carbon::now();
        $errCnt = DBCommand::select([
            'select' => 'COUNT(*)',
            'from'   => self::DB_TABLE_LOG,
            'where'  => [[
                'clause' => ':code: = {code} AND :ip: = {ip} AND :time: > {failPeriod}',
                'values' => [$code, $event['ip'], $Now->subMinutes(self::FAIL_PERIOD)]
            ]]
        ], DBCommand::OUTPUT_FIRST_CELL);
        if ($errCnt >= self::FAIL_THRESHOLD) {
            self::lockout($code, $event['ip']);
        }
    }

    /**
     * Бан IP.
     *
     * @param  int     $code  Код события (403, 404 и т.п.)
     * @param  string  $ip    IP
     */
    protected static function lockout(int $code, string $ip): void
    {
        if (!Cfg::GUARD_IS_ON) {
            return;
        }
        //if (self::ipInWhiteList($ip)) {
        //	return;
        //}

        $banCnt = DBCommand::select([
            'select' => 'COUNT(*) + 1',
            'from' => self::DB_TABLE_LOCKOUTS,
            'where'  => [[
                'clause' => ':ip: = {ip} AND :active: = 1',
                'values' => [ $ip ]
            ]]
        ], DBCommand::OUTPUT_FIRST_CELL);
        $Now = Carbon::now();
        DBCommand::insert(
            self::DB_TABLE_LOCKOUTS,
            [
                'time_end' => $Now->addMinutes(self::BANPERIOD * $banCnt * $banCnt),
                'ip' => $ip
            ]
        );

        // Не пора ли банить навечно?
        if ($banCnt >= self::BANLIMIT) {
            $isHtRuleWritten = self::writeHtaccess();
            // Отправка уведомления на email админа
            ClassLoader::loadClass('Notifier');

            $reason = self::CODES_DESCRIPTION[ $code ] ?? 'Другое';
            $hostName = gethostbyaddr($ip);
            $hostName = ($hostName === $ip || $hostName === false)
                ? ''
                : " ({$hostName})";
            $message = "Хост <a href=\"https://ipinfo.io/{$ip}\">{$ip}</a>{$hostName} "
                . "был добавлен в \"чёрный список\". Причина - {$reason} / Достигнут предел временных банов.";
            if (!$isHtRuleWritten) {
                $message .= "\nВнимание! При добавлении правила в файл \""
                    . Cfg::HTACCESS_FILE_NAME . '" возникла ошибка доступа.';
            }
            Notifier::adminNotice('Уведомление о блокировке', $message);
        }

        Response::setStatusCode(Response::STATUS_FORBIDDEN);
        Response::close(Response::STATUS_FORBIDDEN);
    }

    /**
     * Заблокирован ли IP текущего пользователя?
     *
     * @return bool
     */
    protected static function checkLock(): bool
    {
        if (!Cfg::GUARD_IS_ON) {
            return false;
        }

        $ip = Request::getUserIP();
        if (self::ipInWhiteList($ip)) {
            return false;
        }

        return (bool) DBCommand::select([
            'select' => '1',
            'from'   => self::DB_TABLE_LOCKOUTS,
            'where'  => [[
                'clause' => ':ip: = {ip} AND :time_end: > {now} AND :active: = 1',
                'values' => [$ip, Carbon::now()]
            ]]
        ], DBCommand::OUTPUT_FIRST_CELL);
    }

    /** Первоначальная базовая проверка */
    public static function initialCheck(): void
    {
        if (!Cfg::GUARD_IS_ON) {
            return;
        }

        self::requestURICheck();

        if (self::checkLock()) {
            Response::setStatusCode(Response::STATUS_FORBIDDEN);
            Response::close(Response::STATUS_FORBIDDEN);
        }
    }

    /**
     * Установка максимально допустимой длины запрошенного пользователем URI.
     *
     * @param  int  $value  `-1` - отключение проверки подозрительных URL
     */
    public static function setRequestUriMaxLength(int $value): void
    {
        self::$requestUriMaxLength = max($value, -1);
    }

    /** Проверка переданного пользователем URI на аномалии */
    public static function requestURICheck(): void
    {
        if (self::$requestUriMaxLength === -1) {
            return;
        }

        $uri = Request::getUri();
        if (isset($uri[self::$requestUriMaxLength + 1])) { // eq. "strlen(...) > $requestUriMaxLength"
            Response::setStatusCode(Response::STATUS_URI_TOO_LONG);
            Response::close(Response::STATUS_URI_TOO_LONG);
        }

        // base64 | concat | eval( | union | select
        // /admin.php | /admin/index.php | /admin/login | /administrator | /adminzone |/index.php/admin/
        // /bitrix/ | /manager/ | /netcat/ | /user/
        // /language/ | /libraries/ | /components/ | /htaccess.txt
        // /wp- | /xmlrpc.php
        // /phpmyadmin/ | backup | config | .bak | .old
        if (preg_match('/(base64|concat|eval\(|union|select|\/admin(\.php|\/(index|login)(\.php)*)|\/administrator|\/adminzone|\/index\.php\/admin\/|\/bitrix\/|\/manager\/|\/netcat\/|\/user\/|\/language\/|\/libraries\/|\/components\/|\/htaccess\.txt|\/wp-|\/xmlrpc\.php|\/phpmyadmin\/|backup|config|\.(bak|old))/i', $uri)) {
            self::logEvent(self::CODES['forbidden'], null, $uri, true);
        }
    }

    /**
     * Получение текста предупреждения по коду события.
     *
     * @param   int  $code  Код события (403, 404 и т.п.)
     * @return  string
     */
    public static function getWarning(int $code): string
    {
        if ($code === self::CODES['unauthorized']) {
            return 'Когда количество неудачных попыток аутентификации достигнет '
                . self::FAIL_THRESHOLD . ', Ваш IP-адрес будет заблокирован';
        }

        return '';
    }

    /**
     * Генерация секции Guard'а для Access-файла Apache 2.4+.
     *
     * @return string
     */
    protected static function makeRulesHtaccess(): string
    {
        $ipCol = DBCommand::qC('ip');
        $ips = DBCommand::select([
            'select' => [['ip', 'cnt' => 'COUNT(*)']],
            'from'   => self::DB_TABLE_LOCKOUTS,
            'where'  => "{$ipCol} AND active = 1",
            'group'  => $ipCol,
            'having' => DBCommand::qC('cnt') . ' >= ' . self::BANLIMIT,
            'order'  => $ipCol
        ], DBCommand::OUTPUT_FIRST_COLUMN);
        $blackList = '';
        foreach ($ips as $ip) {
            $blackList .= "  Require not ip {$ip}\n";
        }

        if ($blackList) {
            return self::ACCESS_FILE_MARKER_BEGIN . PHP_EOL
                . '<RequireAll>' . PHP_EOL
                . '  Require all granted' . PHP_EOL
                . $blackList
                . '</RequireAll>' . PHP_EOL
                . self::ACCESS_FILE_MARKER_END . PHP_EOL
                . PHP_EOL;
        }

        return '';
    }

    /**
     * Запись в Access-файл Apache секции Guard'а.
     *
     * @return  bool  Успешно ли прошла запись
     */
    protected static function writeHtaccess(): bool
    {
        $htaccessPath = Cfg::DIR_ROOT . Cfg::HTACCESS_FILE_NAME;
        if (!@chmod($htaccessPath, Cfg::CHMOD_FILES) || !is_writable($htaccessPath)) {
            return false;
        }

        $htaccessContent = file_get_contents($htaccessPath);
        $pattern = '/' . self::ACCESS_FILE_MARKER_BEGIN . '.*' . self::ACCESS_FILE_MARKER_END . '\s*/s';
        $htaccessContent = preg_replace($pattern, '', $htaccessContent);
        $result = FsFile::make($htaccessPath, self::makeRulesHtaccess() . $htaccessContent);
        chmod($htaccessPath, Cfg::HTACCESS_FILE_CHMOD);

        return $result;
    }

    /**
     * Построение списка столбцов для выборки из таблицы в БД.
     *
     * @param   array  $dbColumns  Информация о столбцах таблицы БД
     * @return  string
     */
    protected static function getColumnsSet(array $dbColumns): string
    {
        $select = '';
        $comma = false;
        foreach ($dbColumns as $column => $props) {
            $column = DBCommand::qC($column);
            if ($comma) {
                $select .= ', ';
            } else {
                $comma = true;
            }
            $select .= isset($props['maxlength'])
                ? "SUBSTRING({$column} FROM 1 FOR {$props['maxlength']}) AS {$column}"
                : $column;
        }

        return $select;
    }

    /**
     * Валидация (с корректировкой при необходимости)
     * параметров сортировки, заданных пользователем.
     *
     * @param   array  $dbColumns  Информация о столбцах таблицы БД
     * @param   array  $orderBy    Параметры сортировки вида: ['column' => 'ASC'|'DESC', ...]
     * @return  array
     */
    protected static function prepareOderBy(array $dbColumns, array $orderBy): array
    {
        foreach ($orderBy as $column => $direction) {
            if (!isset($dbColumns[ $column ]['sortable'])) {
                unset($orderBy[ $column ]);
            }
        }

        return $orderBy;
    }

    /**
     * Постраничный вывод логов. Доступен поиск по коду события/IP/логину.
     *
     * @param  ?string  $searchStr  Поисковый запрос
     * @param   int     $limit      Макс. кол-во возвращаемых записей
     * @param   int     $offset     Начиная с какой записи в БД искать
     * @param   array   $orderBy    Параметры сортировки вида: ['column' => 'ASC'|'DESC', ...]
     * @return  array
     */
    public static function findLogs(
        ?string $searchStr = null,
        int $limit = 20,
        int $offset = 0,
        array $orderBy = []
    ): array {
        if (!empty($searchStr)) {
            $searchStr = DBCommand::eV($searchStr);
            $where = [
                ['clause' => ":code: = '{$searchStr}'"],
                ['clause' => ":ip: LIKE '{$searchStr}%'"],
                ['clause' => ":user_login: LIKE '%{$searchStr}%'"]
            ];
        } else {
            $where = null;
        }

        $dbRows = DBCommand::select([
            'calc_found_rows' => true,
            'select'          => self::getColumnsSet(self::DB_COLUMNS_LOG),
            'from'            => self::DB_TABLE_LOG,
            'where'           => $where,
            'order'           => self::prepareOderBy(self::DB_COLUMNS_LOG, $orderBy),
            'limit'           => $limit,
            'offset'          => $offset
        ]);
        DBCommand::calcFoundRows();
        self::$foundRowsLogs = DBCommand::getFoundRows();

        return $dbRows;
    }

    /**
     * Получение общего количества записей, найденных ранее посредством [[self::findLogs()]].
     *
     * @return int
     */
    public static function countLogs(): int
    {
        return self::$foundRowsLogs;
    }

    /**
     * Постраничный вывод блокировок. Доступен поиск по IP.
     *
     * @param  ?string  $searchStr  Поисковый запрос
     * @param   int     $limit      Макс. кол-во возвращаемых записей
     * @param   int     $offset     Начиная с какой записи в БД искать
     * @param   array   $orderBy    Параметры сортировки вида: ['column' => 'ASC'|'DESC', ...]
     * @return  array
     */
    public static function findLockouts(
        ?string $searchStr = null,
        int $limit = 20,
        int $offset = 0,
        array $orderBy = []
    ): array {
        if (!empty($searchStr)) {
            $where = [[
                'clause' => ':ip: LIKE \'{searchStr}%\'',
                'values' => [ $searchStr ]
            ]];
        } else {
            $where = null;
        }

        $dbRows = DBCommand::select([
            'calc_found_rows' => true,
            'select'          => self::getColumnsSet(self::DB_COLUMNS_LOCKS),
            'from'            => self::DB_TABLE_LOCKOUTS,
            'where'           => $where,
            'order'           => self::prepareOderBy(self::DB_COLUMNS_LOCKS, $orderBy),
            'limit'           => $limit,
            'offset'          => $offset
        ]);
        DBCommand::calcFoundRows();
        self::$foundRowsLocks = DBCommand::getFoundRows();

        return $dbRows;
    }

    /**
     * Получение общего количества записей, найденных ранее посредством [[self::findLockouts()]].
     *
     * @return int
     */
    public static function countLockouts(): int
    {
        return self::$foundRowsLocks;
    }
}
