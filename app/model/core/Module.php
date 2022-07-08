<?php

ClassLoader::loadClass('Properties');

/**
 * Класс Module.
 *
 * Подключение модулей CMF.
 *
 * @author Dmitriy Lunin
 */
class Module extends Properties
{
    /**
     * Подключение модуля
     *
     * @param  string  $module  Идентификатор модуля
     * @param  string  $dir     Директория контроллера
     * @param  string  $file    Файл контроллера
     */
    public function show(string $dir = '', string $file = '', string $module = ''): void
    {
        try {
            if ( ! empty( $dir ) && ! empty( $file ) ) {
                $controller = Cfg::DIR_VIEW . $dir . Cfg::DS . $file;
            } elseif (!empty($module)) {
                $controller = Cfg::DIR_MODULES . $module . Cfg::DS . 'controller';
            } else {
                throw new  RuntimeException('Invalid parameters');
            }

            if (
                (Application::isMobileSite() && ($filePath = "{$controller}.m.php") && FsFile::isExists($filePath))
                || (($filePath = "{$controller}.php") && FsFile::isExists($filePath))
            ) {
                $closure = function() use ($filePath) {
                    require $filePath;
                };
                $closure();
            } else {
                throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, $filePath );
            }
        } catch (Throwable $Ex) {
            Logger::registerException($Ex);
        } finally {
            Logger::showExceptions();
        }
    }
}
