<?php
/**
 * Статичный класс FsFile.
 *
 * Методы для работы с файлами.
 *
 * @author Dmitry Lunin
 */
class FsFile
{
    /**
     * Список некоторых типов MIME.
     *
     * @link  http://www.freeformatter.com/mime-types-list.html
     */
    public const MIME_TYPES = [
        'bin'  => 'application/octet-stream',
        'doc'  => 'application/msword',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'odp'  => 'application/vnd.oasis.opendocument.presentation',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt'  => 'application/vnd.oasis.opendocument.text',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf'  => 'application/pdf',
        '7z'   => 'application/x-7z-compressed',
        'rar'  => 'application/x-rar-compressed',
        'zip'  => 'application/zip',
        'bmp'  => 'image/bmp',
        'gif'  => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'txt'  => 'text/plain',
    ];

    /**
     * Проверка доступности файла.
     *
     * @param   string  $path  Полное имя файла
     * @return  bool
     */
    public static function isExists(string $path): bool
    {
        // is_readable() может ошибочно возвращать FALSE для сетевых имён (UNC)
        return strpos($path, '\\\\') === 0
            ? is_file($path)
            : is_file($path) && is_readable($path);
    }

    /**
     * Проверка безопасности чтения файла.
     *
     * @param   string  $path  Полное имя файла
     * @return  bool
     */
    public static function isSafeToRead(string $path): bool
    {
        return self::isExists($path) && !preg_match('#^[a-z]+://#i', $path);
    }

    /**
     * Получение размера файла в human-friendly виде.
     *
     * @param   string  $path  Полный путь к файлу
     * @return  string
     */
    public static function getSize(string $path): string
    {
        if (!self::isExists($path)) {
            throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, $path );
        }

        $size = filesize($path);

        if ($size >= 1073741824) {
            $size = round($size / 1073741824, 2) . ' G';
        } elseif ($size >= 1048576) {
            $size = round($size / 1048576, 2) . ' M';
        } elseif ($size >= 1024) {
            $size = round($size / 1024, 2) . ' K';
        } else {
            $size .= ' B';
        }

        return $size;
    }

    /**
     * Вычленение расширения файла - части имени после последней точки.
     *
     * @param   string  $name  Имя файла
     * @return  string
     */
    public static function getExtension(string $name): string
    {
        if (strrpos($name, '.') === false) {
            return '';
        }

        if (($cropLen = strpos($name, '?')) !== false) {
            $name = substr($name, 0, $cropLen);
        }

        return mb_strtolower(substr(strrchr($name, '.'), 1), Cfg::CHARSET);
    }

    /**
     * Определение MIME-типа файла по его имени.
     *
     * @param   string  $name  Имя файла
     * @return  string
     */
    public static function getTypeByName(string $name): string
    {
        $ext = self::getExtension($name);

        return self::MIME_TYPES[ $ext ] ?? self::MIME_TYPES['bin'];
    }

    /**
     * Определение MIME-типа содержимого файла.
     * Если расширение Fileinfo не установлено, тип определяется на основе имени файла.
     *
     * @param   string  $path  Путь к файлу
     * @return  string
     */
    public static function getType(string $path): string
    {
        if (!self::isSafeToRead($path)) {
            throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, $path );
        }

        if (extension_loaded('fileinfo')) {
            $info = finfo_open(FILEINFO_MIME_TYPE);

            if ($info !== false) {
                $type = finfo_file($info, $path);
                finfo_close($info);

                if ($type !== false) {
                    return $type;
                }
            }
        }

        return self::getTypeByName($path);
    }

    /**
     * Запись в файл заданного содержимого с проверкой количества записанных байт.
     *
     * @param   string  $path     Полное имя файла
     * @param   string  $content  Содержимое файла
     * @param   string  $mode     Режим для fopen()
     * @return  bool    TRUE в случае успеха, иначе FALSE
     */
    public static function writeTo(string $path, string $content, string $mode): bool
    {
        return ($handle = fopen($path, $mode)) !== false
            && ($written = fwrite($handle, $content)) !== false
            && $written === byteLength($content)
            && fclose($handle);
    }

    /**
     * Создание файла с заданным содержимым.
     * Если такой файл уже существует, он будет перезаписан.
     *
     * @see     FsFile::writeTo()
     * @param   string  $path
     * @param   string  $content
     * @return  bool
     */
    public static function make(string $path, string $content): bool
    {
        return self::writeTo($path, $content, 'wb');
    }

    /**
     * Добавление в файл заданного содержимого.
     *
     * @see     FsFile::writeTo()
     * @param   string  $path
     * @param   string  $content
     * @return  bool
     */
    public static function addTo(string $path, string $content): bool
    {
        return self::writeTo($path, $content, 'ab');
    }

    /**
     * Загрузка файла на сервер.
     * Возвращается массив вида [
     *   'name' => 'оригинальное-имя-файла',
     *   'tmp_name' => 'путь-к-временному-файлу'
     * ].
     *
     * @param   array  $file     Информация о загруженном файле, аналогично $_FILES['userfile']
     * @param   bool   $isTemp   Удалить файл(ы) по завершении работы скрипта?
     * @param   int    $maxSize  Максимальный допустимый размер файла в байтах
     * @return  array
     */
    protected static function uploadFile(array $file, bool $isTemp = true, int $maxSize = 0): array
    {
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new FilesUploadEx( FilesUploadEx::NO_FILES );
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new FilesUploadEx( FilesUploadEx::SIZE_IS_INVALID );
            default:
                throw new FilesUploadEx();
        }

        if ($maxSize && $file['size'] > $maxSize) {
            throw new FilesUploadEx( FilesUploadEx::SIZE_IS_INVALID, $maxSize );
        }

        $tmpFile = Cfg::DIR_TMP_FILES . Randomizer::getHex();

        if (move_uploaded_file($file['tmp_name'], $tmpFile) === false) {
            throw new FilesUploadEx();
        }

        if ($isTemp) {
            register_shutdown_function('unlink', $tmpFile);
        }

        chmod($tmpFile, Cfg::CHMOD_FILES);
        $result['name'] = $file['name'];
        $result['tmp_name'] = $tmpFile;

        return $result;
    }

    /**
     * Загрузка одного или нескольких файлов на сервер.
     * В случае загрузки только одного файла возвращается массив вида [
     *   'name' => 'оригинальное-имя-файла',
     *   'tmp_name' => 'путь-к-временному-файлу'
     * ],
     * если же файлов несколько, возвращается список таких массивов.
     *
     * @param   string  $nameField  Значение атрибута "name" поля выбора файла
     * @param   bool    $isTemp     Удалить файл(ы) по завершении работы скрипта
     * @param   int     $maxSize    Максимально допустимый размер файла в байтах
     * @return  array
     */
    public static function uploadFiles(string $nameField, bool $isTemp = true, int $maxSize = 0): array
    {
        if (!isset($_FILES[ $nameField ])) {
            throw new FilesUploadEx( FilesUploadEx::NO_FILES );
        }

        $files = &$_FILES[ $nameField ];

        if (is_array($files['error'])) {
            $result = [];
            foreach ($files['error'] as $index => $error) {
                if (
                    $files['tmp_name'][ $index ] == null
                    && isset($files['tmp_name']['1'])
                ) { //special for IE
                    break;
                }

                $result[] = self::uploadFile(
                    [
                        'name' => $files['name'][ $index ],
                        'type' => $files['type'][ $index ],
                        'size' => $files['size'][ $index ],
                        'tmp_name' => $files['tmp_name'][ $index ],
                        'error' => $error
                    ],
                    $isTemp,
                    $maxSize
                );
            }
        } else {
            $result = self::uploadFile($files, $isTemp, $maxSize);
        }

        return $result;
    }
}
