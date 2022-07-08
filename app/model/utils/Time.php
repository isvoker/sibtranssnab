<?php
/**
 * Class Time.
 *
 * Методы для работы с датой и временем.
 *
 * @author Dmitry Lunin
 */
class Time
{
    /* Кол-во секунд в ... */
    public const MINUTE = 60;
    public const HOUR   = 3600;
    public const DAY    = 86400;
    public const WEEK   = 604800;
    public const MONTH  = 2592000; // 30 days
    public const YEAR   = 31536000;

    public const HUMAN_DATE_TIME = 'd.m.Y H:i:s';
    public const HUMAN_DATE      = 'd.m.Y';
    public const HUMAN_TIME      = 'H:i:s';

    public const SQL_DATE_TIME = 'Y-m-d H:i:s';
    public const SQL_DATE      = 'Y-m-d';
    public const SQL_TIME      = 'H:i:s';

    public const MONTHS_IN_DATE = [
        1 => 'января',   2 => 'февраля',  3 => 'марта',
        4 => 'апреля',   5 => 'мая',      6 => 'июня',
        7 => 'июля',     8 => 'августа',  9 => 'сентября',
        10 => 'октября', 11 => 'ноября',  12 => 'декабря'
    ];

    public const DAYS_OF_WEEK = [
        0 => 'Понедельник',
        1 => 'Вторник',
        2 => 'Среда',
        3 => 'Четверг',
        4 => 'Пятница',
        5 => 'Суббота',
        6 => 'Воскресенье'
    ];

    public const DAYS_OF_WEEK_SHORT = [
        0 => 'Пн', 1 => 'Вт', 2 => 'Ср', 3 => 'Чт', 4 => 'Пт', 5 => 'Сб', 6 => 'Вс'
    ];

    /**
     * Преобразование времени в метку времени Unix.
     * Если время не задано, то возвращается текущее значение.
     *
     * @param   string|int|null  $time  Строка даты/времени
     * @return  int
     */
    public static function toStamp($time = null): int
    {
        if (is_null($time)) {
            return $_SERVER['REQUEST_TIME'];
        }

        if (is_numeric($time)) {
            return (int) $time;
        }

        if (is_string($time)) {
            $stamp = strtotime($time);
            if ($stamp !== false) {
                return $stamp;
            }
        }

        throw new InvalidArgumentException('Invalid format of datetime');
    }

    /**
     * Форматирование времени согласно переданному формату.
     * Если время не задано, то используется текущее.
     *
     * @param   string           $format  Формат, принимаемый функцией date()
     * @param   string|int|null  $time    Строка даты/времени или метка времени
     * @return  string
     */
    public static function get(string $format, $time = null): string
    {
        $time = self::toStamp($time);
        return $time > 0 ? date($format, $time) : '';
    }

    /**
     * Получение текущего времени с точностью до микросекунд в заданном формате.
     *
     * @param   string  $format  Формат, принимаемый функцией date()
     * @return  string
     */
    public static function getExactCurrentTime(string $format = self::SQL_DATE_TIME): string
    {
        [$sec, $msec] = explode('.', (string)microtime(true));
        return date($format, $sec) . '.' . ($msec ?: '0');
    }

    /**
     * Форматирование времени в формат [[self::HUMAN_DATE]].
     *
     * @see     Time::get()
     * @param   string|int|null  $time
     * @return  string
     */
    public static function toDate($time = null): string
    {
        return self::get(self::HUMAN_DATE, $time);
    }

    /**
     * Форматирование времени в формат [[self::HUMAN_TIME]].
     *
     * @see     Time::get()
     * @param   string|int|null  $time
     * @return  string
     */
    public static function toTime($time = null): string
    {
        return self::get(self::HUMAN_TIME, $time);
    }

    /**
     * Форматирование времени в формат [[self::HUMAN_DATE_TIME]].
     *
     * @see     Time::get()
     * @param   string|int|null  $time
     * @return  string
     */
    public static function toDateTime($time = null): string
    {
        return self::get(self::HUMAN_DATE_TIME, $time);
    }

    /**
     * Форматирование времени в формат [[self::SQL_DATE]].
     *
     * @see     Time::get()
     * @param   string|int|null  $time
     * @return  string
     */
    public static function toSQLDate($time = null): string
    {
        return self::get(self::SQL_DATE, $time);
    }

    /**
     * Форматирование времени в формат [[self::SQL_TIME]].
     *
     * @see     Time::get()
     * @param   string|int|null  $time
     * @return  string
     */
    public static function toSQLTime($time = null): string
    {
        return self::get(self::SQL_TIME, $time);
    }

    /**
     * Форматирование времени в формат [[self::SQL_DATE_TIME]].
     *
     * @see     Time::get()
     * @param   string|int|null  $time
     * @return  string
     */
    public static function toSQLDateTime($time = null): string
    {
        return self::get(self::SQL_DATE_TIME, $time);
    }

    /**
     * Получение названия месяца по его порядковому номеру.
     *
     * @param   int  $monthNumber
     * @return  string
     */
    public static function getMonthName(int $monthNumber): string
    {
        return self::MONTHS_IN_DATE[$monthNumber] ?? 'unknown';
    }

    /**
     * Форматирование заданного (или текущего) времени в текст (14 Июля 2011).
     *
     * @param   string|int|null  $time  Строка даты/времени
     * @return  string
     */
    public static function toText($time = null): string
    {
        $time = self::toStamp($time);
        return $time > 0
            ? date('d', $time) . ' ' . self::getMonthName(date('m', $time)) . ' ' . date('Y', $time)
            : '';
    }

    /**
     * Получение названия дня недели по его порядковому номеру
     *
     * @param   int   $dayNumber  Номер дня. 0 - 'Понедельник'.
     * @param   bool  $short      Требуется короткое обозначение
     * @return  string
     */
    public static function getWeekdayName(int $dayNumber, bool $short = false): string
    {
        if ($short) {
            return self::DAYS_OF_WEEK_SHORT[$dayNumber] ?? 'unknown';
        }
        return self::DAYS_OF_WEEK[$dayNumber] ?? 'unknown';
    }

    /**
     * Проверка корректности даты по заданному формату.
     *
     * @param   string  $time
     * @param   string  $format
     * @return  bool
     */
    public static function validate(string $time, string $format): bool
    {
        $DT = DateTime::createFromFormat($format, $time);
        return $DT && $DT->format($format) === $time;
    }

    /**
     * Проверка корректности даты в формате [[self::SQL_DATE]].
     * Некрасивый, но очень быстрый вариант.
     *
     * @param   string  $time
     * @return  bool
     */
    public static function validateSQLDate(string $time): bool
    {
        return isset($time[9]) && !isset($time[10])
            && $time[4] === '-'
            && $time[7] === '-'
            && checkdate(substr($time, 5, 2), substr($time, 8, 2), substr($time, 0, 4));
    }

    /**
     * Вычисление возраста по дате рождения.
     * В случае некорректной даты рождения возвращается FALSE.
     * Обе даты должны быть в формате [[self::SQL_DATE]].
     *
     * @param   string  $birthday  Дата рождения
     * @param  ?string  $date      На какую дату считать возраст. Если не задано или некорректно - на текущую
     * @return  int
     */
    public static function calcAge(string $birthday, ?string $date = null): int
    {
        if (!self::validateSQLDate($birthday)) {
            return false;
        }

        if ($date !== null && self::validateSQLDate($date)) {
            return (int) ((str_replace('-', '', $date) - str_replace('-', '', $birthday)) / 10000);
        }

        [$year, $month, $day] = explode('-', $birthday);
        $yearDiff  = (int) date('Y') - $year;
        $monthDiff = (int) date('m') - $month;
        $dayDiff   = (int) date('d') - $day;
        if (
            $monthDiff < 0
            || (($monthDiff === 0) && ($dayDiff < 0))
        ) {
            --$yearDiff;
        }

        return $yearDiff;
    }

    /**
     * Вычисление номера юлианского дня (JDN) по дате григорианского календаря.
     * @link  https://en.wikipedia.org/wiki/Julian_day
     *
     * @param   string|int|null  $time  Строка даты/времени или метка времени
     * @return  int
     */
    public static function getJDN(int $time = null): int
    {
        $time = self::toStamp($time);
        $a = floor((14 - date('n', $time)) / 12);
        $y = date('Y', $time) + 4800 - $a;
        $m = date('n', $time) + 12 * $a - 3;

        return date('j', $time)
            + floor((153 * $m + 2) / 5)
            + 365 * $y
            + floor($y / 4)
            - floor($y / 100)
            + floor($y / 400) - 32045;
    }
}
