<?php
/**
 * Статичный класс FsArchive.
 *
 * Методы для работы с архивами.
 *
 * @author Dmitry Lunin
 */
class FsArchive
{
    /**
     * Добавление в ZIP-архив указанных файлов.
     * Если 'имя_файла_внутри_архива' равно NULL, будет использован 'путь_к_файлу_для_архивации'.
     *
     * @param   array   $files      Список ['путь_к_файлу_для_архивации' => 'имя_файла_внутри_архива']
     * @param   string  $arcPath    Полное имя архива
     * @param   bool    $overwrite  Перезаписать архив, если он уже существует?
     * @param   bool    $deleteSrc  Удалять ли исходные файлы после архивации?
     * @return  bool    TRUE в случае успеха, иначе FALSE
     */
    public static function makeZip(
        array $files,
        string $arcPath,
        bool $overwrite = false,
        bool $deleteSrc = false
    ): bool {
        if (
            FsFile::isSafeToRead($arcPath)
            && !($overwrite && is_writable($arcPath))
        ) {
			// архив уже есть, и мы его не перезаписываем
            return false;
        }

        foreach ($files as $srcPath => $dstName) {
            if (!FsFile::isSafeToRead($srcPath)) {
                unset($files[ $srcPath ]);
            }
        }
        if (empty($files)) {
            return false;
        }

        $Zip = new ZipArchive();
        if ($Zip->open($arcPath, $overwrite ? ZipArchive::OVERWRITE : ZipArchive::CREATE) !== true) {
            return false;
        }

        foreach ($files as $srcPath => &$dstName) {
            if (!is_string($dstName)) {
                $dstName = $srcPath;
            }
            if ($Zip->addFile($srcPath, $dstName) === true) {
                $dstName = true;
            }
        }
        unset($dstName);

        $zipExists = $Zip->close() && is_file($arcPath);
        if ($zipExists && $deleteSrc) {
            foreach ($files as $srcPath => $canBeRemove) {
                if ($canBeRemove) {
                    unlink($srcPath);
                }
            }
        }

        return $zipExists;
    }

    /**
     * Извлечение файлов из ZIP-архива.
     *
     * @param   string  $arcPath  Полное имя архива
     * @param   string  $toPath   Полный путь к месту назначения
     * @return  bool    TRUE в случае успеха, иначе FALSE
     */
    public static function extractZip(string $arcPath, string $toPath): bool
    {
        if (!FsFile::isSafeToRead($arcPath)) {
            throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, $arcPath );
        }

        $Zip = new ZipArchive();

        return $Zip->open($arcPath) === true
            && $Zip->extractTo($toPath)
            && $Zip->close();
    }

    /**
     * GZ-архивация файла.
     *
     * @param   string  $filePath  Полный путь к исходному файлу
     * @param   string  $arcPath   Полное имя архива
     * @return  bool    TRUE в случае успеха, иначе FALSE
     */
    public static function makeGZ(string $filePath, string $arcPath): bool
    {
        if (!FsFile::isSafeToRead($filePath)) {
            throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, $filePath );
        }

        $gz = gzopen($arcPath, 'w9');
        if ($gz === false) {
            return false;
        }

        gzwrite($gz, file_get_contents($filePath));
        gzclose($gz);

        return true;
    }
}
