<?php
/**
 * Статичный класс FsDirectory.
 *
 * Методы для работы с директориями файловой системы.
 *
 * @author Dmitry Lunin
 */
class FsDirectory
{
    /**
     * Замена разделителя пути с '/' на DIRECTORY_SEPARATOR при их несовпадении.
     * Предполагается, что в путях ФС всегда используется прямой слэш - '/'.
     *
     * @param   string  $path  Путь
     * @return  string
     */
    public static function normalizePath(string $path): string
    {
        if (Cfg::DS !== '/') {
            $path = str_replace('/', Cfg::DS, $path);
        }

        return $path;
    }

    /**
     * Преобразование пути в относительный к заданной директории.
     *
     * @param   string  $path  Путь к файлу
     * @param   string  $from  Имя директории, от которой надо получить путь
     * @return  string
     */
    public static function getRelativePath(string $path, string $from): string
    {
        if (empty($path)) {
            return '';
        }

        $fromDir = Cfg::DS . $from . Cfg::DS;
        if (($substr = strstr($path, $fromDir)) === false) {
            return $path;
        }

        return $substr;
    }

    /**
     * Создание директории внутри сайта, если такой ещё не существует.
     *
     * @param  string  $path  Путь к директории относительно корня сайта
     */
    public static function make(string $path): void
    {
        if (
            !is_dir(Cfg::DIRS_ROOT . $path)
            && !mkdir(Cfg::DIRS_ROOT . $path, Cfg::CHMOD_FOLDERS, true)
            && !is_dir(Cfg::DIRS_ROOT . $path)
        ) {
            throw new FilesystemEx( FilesystemEx::DIRECTORY_IS_NOT_CREATED, $path);
        }
    }

    /**
     * Создание временной директории.
     *
     * @link  https://stackoverflow.com/questions/1707801
     * @return  string  Путь к созданной директории
     */
    public static function makeTemporary(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        if (
            mkdir($tempFile, Cfg::CHMOD_FOLDERS, true)
            && is_dir($tempFile)
        ) {
            return $tempFile;
        }

        throw new FilesystemEx( FilesystemEx::DIRECTORY_IS_NOT_CREATED, $tempFile);
    }

    /**
     * Удаление всего содержимого директории.
     *
     * @param   string  $path  Полный путь к директории
     * @return  bool    TRUE в случае успеха, иначе FALSE
     */
    public static function clear(string $path): bool
    {
        if (!is_dir($path)) {
            throw new FilesystemEx(FilesystemEx::IS_NOT_A_DIRECTORY, $path);
        }

        $objects = scandir($path, SCANDIR_SORT_NONE);
        foreach ($objects as $object) {
            if ($object !== '.' && $object !== '..') {
                if (filetype($path . Cfg::DS . $object) === 'dir') {
                    self::rmRecursively($path . Cfg::DS . $object);
                } else {
                    unlink($path . Cfg::DS . $object);
                }
            }
        }
        reset($objects);

        return true;
    }

    /**
     * Рекурсивное удаление директории.
     *
     * @param   string  $path  Полный путь к директории
     * @return  bool    TRUE в случае успеха, иначе FALSE
     */
    public static function rmRecursively(string $path): bool
    {
        if (self::clear($path)) {
            return rmdir($path);
        }

        throw new FilesystemEx(FilesystemEx::DIRECTORY_IS_NOT_DELETED, $path);
    }


    // specific functions

    /**
     * Замена разделителя пути с DIRECTORY_SEPARATOR на '/' при их несовпадении.
     * Предполагается, что в путях ФС всегда используется прямой слэш - '/'.
     *
     * @param   string  $path  Путь
     * @return  string
     */
    public static function normalizeWebPath(string $path): string
    {
        if (Cfg::DS !== '/') {
            $path = str_replace(Cfg::DS, '/', $path);
        }

        return $path;
    }
}
