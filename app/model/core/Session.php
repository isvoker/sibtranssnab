<?php
/**
 * Статичный класс Session.
 *
 * Набор методов для работы с сессией пользователя.
 *
 * @author Dmitriy Lunin
 */
class Session
{
    /**
     * Инициализация сессии.
     */
    public static function open(): void
    {
        if (self::isActive()) {
            return;
        }

        $sessionStarted = session_start([
            'cookie_httponly' => true
        ]);

        if ($sessionStarted !== true) {
            throw new FeatureNotAvailableEx('Failed to start the session');
        }
    }

    /**
     * Механизм сессий включён и сессия создана?
     *
     * @return bool
     */
    public static function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Получение параметров cookie сессии.
     *
     * @return array
     */
    public static function getCookieParams(): array
    {
        return session_get_cookie_params();
    }

    /**
     * Генерация и обновление идентификатора текущей сессии.
     *
     * @link  https://php.net/session_regenerate_id
     *
     * @param   bool  $deleteOldSession  Удалять ли старые файлы сессии
     * @return  bool  TRUE в случае успеха, иначе - FALSE
     */
    public static function regenerateID(bool $deleteOldSession = false): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Удаление всех переменных, хранящихся в сессии.
     */
    public static function unsetAll(): void
    {
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[ $key ]);
        }
    }

    /**
     * Получение из сессии значения переменной заданной группы.
     *
     * @param   string  $group  Название группы
     * @param   string  $name   Имя переменной
     * @return  mixed
     */
    public static function get(string $group, string $name)
    {
        return $_SESSION['sensei'][ $group ][ $name ] ?? null;
    }

    /**
     * Получение из сессии всех переменных заданной группы.
     *
     * @param   string  $group  Название группы
     * @return  array
     */
    public static function getAll(string $group): array
    {
        return $_SESSION['sensei'][ $group ] ?? [];
    }

    /**
     * Установка в сессии значения переменной заданной группы.
     *
     * @param  string  $group  Название группы
     * @param  string  $name   Имя переменной
     * @param  mixed   $value  Значение
     */
    public static function set(string $group, string $name, $value): void
    {
        if (!isset($_SESSION['sensei'])) {
            $_SESSION['sensei'] = [];
        }

        if (!isset($_SESSION['sensei'][ $group ])) {
            $_SESSION['sensei'][ $group ] = [];
        }

        $_SESSION['sensei'][ $group ][ $name ] = $value;
    }

    /**
     * Удаление из сессии переменной заданной группы.
     * Если имя переменной не задано, удаляются все переменный группы.
     *
     * @param  string  $group  Название группы
     * @param  ?string  $name   Имя переменной
     */
    public static function delete(string $group, ?string $name = null): void
    {
        if (!isset($_SESSION['sensei'])) {
            $_SESSION['sensei'] = [];
            return;
        }

        if (isset($_SESSION['sensei'][ $group ])) {
            if (is_null($name)) {
                $_SESSION['sensei'][ $group ] = [];
            } else {
                unset($_SESSION['sensei'][ $group ][ $name ]);
            }
        }
    }
}
