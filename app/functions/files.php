<?php
/**
 * Утилиты для работы с директориями и файлами
 */

/**
 * @see FsDirectory::make()
 * @deprecated
 */
function makeDir(string $path)
{
    FsDirectory::make($path);
}

/**
 * @see FsDirectory::makeTemporary()
 * @deprecated
 */
function tempDir(): string
{
    return FsDirectory::makeTemporary();
}

/**
 * @see FsDirectory::clear()
 * @deprecated
 */
function clearDir(string $path): bool
{
    return FsDirectory::clear($path);
}

/**
 * @see FsDirectory::rmRecursively()
 * @deprecated
 */
function rrmdir(string $path): bool
{
    return FsDirectory::rmRecursively($path);
}

/**
 * @see FsDirectory::normalizePath()
 * @deprecated
 */
function preparePath(string $path): string
{
    return FsDirectory::normalizePath($path);
}

/**
 * @see FsFile::writeToFile()
 * @deprecated
 */
function writeToFile(string $path, string $content, string $mode): bool
{
    return FsFile::writeTo($path, $content, $mode);
}

/**
 * @see FsFile::make()
 * @deprecated
 */
function makeFile(string $path, string $content): bool
{
    return FsFile::make($path, $content);
}

/**
 * @see FsFile::addTo()
 * @deprecated
 */
function addToFile(string $path, string $content): bool
{
    return FsFile::addTo($path, $content);
}

/**
 * @see FsFile::isExists()
 * @deprecated
 */
function isFileExists(string $path): bool
{
    return FsFile::isExists($path);
}

/**
 * @see FsFile::isSafeToRead()
 * @deprecated
 */
function isFileSafeToRead(string $path): bool
{
    return FsFile::isSafeToRead($path);
}

/**
 * @see FsArchive::makeZip()
 * @deprecated
 */
function createZip(array $files, string $arcPath, bool $overwrite = false, bool $deleteSrc = false): bool
{
    return FsArchive::makeZip($files, $arcPath, $overwrite, $deleteSrc);
}

/**
 * @see FsArchive::extractZip()
 * @deprecated
 */
function extractZip(string $arcPath, string $toPath): bool
{
    return FsArchive::extractZip($arcPath, $toPath);
}

/**
 * @see FsArchive::makeGZ()
 * @deprecated
 */
function createGZ(string $filePath, string $arcPath): bool
{
    return FsArchive::makeGZ($filePath, $arcPath);
}

/**
 * @see FsFile::getSize()
 * @deprecated
 */
function getFileSize(string $path): string
{
    return FsFile::getSize($path);
}

/**
 * @see FsDirectory::getRelativePath()
 * @deprecated
 */
function getRelativePath(string $path, string $from): string
{
    return FsDirectory::getRelativePath($path, $from);
}

/**
 * @see FsFile::getExtension()
 * @deprecated
 */
function getFileExt(string $file): string
{
    return FsFile::getExtension($file);
}

/**
 * @see FsFile::getType()
 * @deprecated
 */
function getFileType(string $path): string
{
    return FsFile::getType($path);
}

/**
 * @see FsFile::getTypeByName()
 * @deprecated
 */
function getFileTypeByName(string $name): string
{
    return FsFile::getTypeByName($name);
}

/**
 * @see FsImage::analyze()
 * @deprecated
 */
function analyzeImage(string $path)
{
    return FsImage::analyze($path);
}

/**
 * @see FsImage::resize()
 * @deprecated
 */
function resizeImage(string $srcPath, array $options = [])
{
    return FsImage::resize($srcPath, $options);
}

/**
 * @see FsFile::uploadFiles()
 * @deprecated
 */
function uploadFiles(string $nameField, bool $isTemp = true, int $maxSize = 0): array
{
    return FsFile::uploadFiles($nameField, $isTemp, $maxSize);
}
