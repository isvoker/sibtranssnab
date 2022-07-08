<?php
/**
 * Статичный класс FsImage.
 *
 * Методы для работы с графическими файлами.
 *
 * @author Dmitry Lunin
 */
class FsImage
{
    /**
     * Определение типа изображения.
     *
     * @param   string  $path  Исходный файл
     * @return  string   Тип файла или FALSE в случае возникновения ошибки
     */
    public static function analyze(string $path)
    {
        if (!$path || !FsFile::isSafeToRead($path)) {
            throw new FilesystemEx(FilesystemEx::FILE_IS_NOT_READABLE, $path);
        }

        switch (exif_imagetype($path)) {
            case IMAGETYPE_GIF:
                return 'gif';
            case IMAGETYPE_JPEG:
                return 'jpg';
            case IMAGETYPE_PNG:
                return 'png';
            case IMAGETYPE_BMP:
                return 'bmp';
            case IMAGETYPE_WEBP:
            	return 'webp';
            default:
                return '';
        }
    }

    /**
     * Изменение размера изображения.
     *
     * Формат целевого файла задаётся явно или определяется расширением целевого или исходного файла.
     * Если путь к целевому файлу не задан, он будет сохранён в DIR_THUMBS рядом с исходным.
     * Если задана только ширина или высота, сохраняются пропорции.
     * Если конечные размеры вообще не заданы, высота принимается равной UI_THUMBS_HEIGHT.
     * Если конечный файл уже существует, и он не старше исходного,
     * сразу возвращается путь к уже имеющемуся файлу.
     *
     * @param  string  $srcPath  Полный путь к исходному изображению
     * @param  array   $options  Необязательные доп. параметры:
     * ~~~
     *   string  $dstPath    Полный путь к целевому файлу.
     *   string  $dstType    Формат целевого изображения (по умолчанию - расширение dstPath|srcPath или 'jpg').
     *   int     $dstW       Результирующая ширина.
     *   int     $dstH       Результирующая высота.
     *   bool    $saveRatio  Сохранять ли пропорции (по умолчанию - TRUE). Возможно обрезание изображения.
     *   bool    $interlace  Надо ли использовать интерлейсинг. Для JPEG это означает прогрессивный формат.
     *   int     $quality    Качество для JPEG / степень сжатия для PNG; от 0 до 100; по умолчанию - 75.
     * ~~~
     * @return  string|bool  Полный путь к целевому файлу или FALSE в случае возникновения ошибки
     */
    public static function resize(string $srcPath, array $options = [])
    {
        $DEFAULT_DST_TYPE = 'jpg';
        $DEFAULT_QUALITY = 75;

        $supportedTypes = [
            IMAGETYPE_GIF => true,
            IMAGETYPE_JPEG => true,
            IMAGETYPE_PNG => true,
            IMAGETYPE_BMP => true,
            IMAGETYPE_WEBP => true,
        ];

        if (!$srcPath || !FsFile::isSafeToRead($srcPath)) {
            throw new FilesystemEx(FilesystemEx::FILE_IS_NOT_READABLE, $srcPath);
        }

        if (!isset($supportedTypes[ exif_imagetype($srcPath) ])) {
            throw new InvalidFileEx(InvalidFileEx::TYPE_IS_INVALID, $srcPath);
        }

        [$srcW, $srcH, $srcType] = getimagesize($srcPath);

        $dstPath = is_string($options['dstPath'] ?? null)
            ? $options['dstPath']
            : null;

        $dstW = is_int($options['dstW'] ?? null)
            ? $options['dstW']
            : null;
        $dstH = is_int($options['dstH'] ?? null)
            ? $options['dstH']
            : null;

        if (!$dstW) {
            if (!$dstH) {
                $dstH = Cfg::UI_THUMBS_HEIGHT;
            }
            $dstW = ceil(($dstH * $srcW) / $srcH);
        } elseif (!$dstH) {
            $dstH = ceil(($dstW * $srcH) / $srcW);
        }

        $srcPathInfo = pathinfo($srcPath);

        if (is_string($options['dstType'] ?? null)) {
            $dstExt = strtolower($options['dstType']);
        } else {
            $dstExt = ($dstPath === null)
                ? $srcPathInfo['extension'] ?? null
                : FsFile::getExtension($dstPath);
        }
        $dstExt = $dstExt ? strtolower($dstExt) : $DEFAULT_DST_TYPE;

        if ($dstPath === null) {
            $dstDir = $srcPathInfo['dirname'] . Cfg::DS;
            if (!is_writable($dstDir)) {
                throw new FilesystemEx(FilesystemEx::DIRECTORY_IS_NOT_WRITABLE, $dstDir);
            }

            $dstDir .=  Cfg::DIR_THUMBS;
            if (
                !is_dir($dstDir)
                && !mkdir($dstDir, Cfg::CHMOD_FOLDERS, true)
            ) {
                throw new FilesystemEx( FilesystemEx::DIRECTORY_IS_NOT_CREATED, $dstDir);
            }

            $dstPath = "{$dstDir}{$srcPathInfo['filename']}-{$dstW}x{$dstH}.{$dstExt}";
        }

        if (
            FsFile::isExists($dstPath)
            && ($cacheTime = filemtime($dstPath))
            && ($sourceTime = filemtime($srcPath))
            && $cacheTime > $sourceTime
        ) {
            return $dstPath;
        }

        $srcX = $srcY = $dstX = $dstY = 0;

        if ($options['saveRatio'] ?? true) {
            $srcRatio = $srcW / $srcH;
            $dstRatio = $dstW / $dstH;

            if ($dstRatio > $srcRatio) { // растягиваем по ширине => сдвиг вверх
                $adjustedH = $dstW / $srcRatio;
                $dstY = round(($dstH - $adjustedH) / 2);
                $dstH = round($adjustedH);
            } elseif ($dstRatio < $srcRatio) { // растягиваем по высоте => сдвиг влево
                $adjustedW = $dstH * $srcRatio;
                $dstX = round(($dstW - $adjustedW) / 2);
                $dstW = round($adjustedW);
            }
        }

        switch ($srcType) {
            case IMAGETYPE_JPEG:
                $srcImage = imagecreatefromjpeg($srcPath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($srcPath);
                break;
            case IMAGETYPE_WEBP:
                $srcImage = imagecreatefromwebp($srcPath);
                break;
            case IMAGETYPE_BMP:
                $srcImage = imagecreatefromwbmp($srcPath);
                break;
            case IMAGETYPE_GIF:
                $srcImage = imagecreatefromgif($srcPath);
                break;
            default:
                throw new InvalidFileEx(InvalidFileEx::TYPE_IS_INVALID, $srcPath);
        }

        $dstImage = imagecreatetruecolor($dstW, $dstH);

        if (
            $dstExt === 'webp'
            || $dstExt === 'png'
            || $dstExt === 'gif'
        ) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
        }

        imagecopyresampled($dstImage, $srcImage, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

        imagedestroy($srcImage);

        // сглаживание
        if (function_exists('imageantialias')) {
            imageantialias($dstImage, true);
        }

        // прогрессивный формат JPEG
        if ($options['interlace'] ?? false) {
            imageinterlace($dstImage, true);
        }

        // "качество"
        if (
            is_int($options['quality'] ?? null)
            && ($options['quality'] > 0)
            && ($options['quality'] <= 100)
        ) {
            $quality = $options['quality'];
        } else {
            $quality = $DEFAULT_QUALITY;
        }
        if ($dstExt === 'png') {
            // для PNG это - степень сжатия, от 0 (нет сжатия) до 9
            $quality = round((100 - $quality) * 4 / 100) + 5; // отображение в интервал [9;5]
        }

        switch ($dstExt) {
            case 'webp':
                $result = imagewebp($dstImage, $dstPath, $quality);
                break;
            case 'png':
                $result = imagepng($dstImage, $dstPath, $quality);
                break;
            case 'bmp':
                $result = imagewbmp($dstImage, $dstPath);
                break;
            case 'gif':
                $result = imagegif($dstImage, $dstPath);
                break;
            case 'jpg':
            case 'jpeg':
            default:
                $result = imagejpeg($dstImage, $dstPath, $quality);
                break;
        }

        imagedestroy($dstImage);

        return $result ? $dstPath : false;
    }

    // specific functions

    /**
     * @see [[self::resize()]]
     *
     * @param   string  $relSrcPath  Относительный путь исходного изображения
     * @param   array   $options
     * @param   string  $from        Имя директории, от которой надо получить путь
     *
     * @return  string  Относительный путь нового изображения или '' в случае возникновения ошибок
     */
    public static function safeResize(string $relSrcPath, array $options = [], $from = Cfg::DIR_FILES_NAME): string
    {
        if (FsFile::isSafeToRead($absPath = FsDirectory::normalizePath(Cfg::DIRS_ROOT . $relSrcPath))) {
            return FsDirectory::normalizeWebPath( FsDirectory::getRelativePath( self::resize($absPath, $options), $from ) );
        }

        return '';
    }
}
