<?php
/**
 * Статичный класс DBDump.
 *
 * Набор методов для создания дампа БД.
 *
 * @author Dmitriy Lunin
 */
class DBDump
{
    /** Директория относительно корня, в которую будут сохраняться дампы */
    protected const BACKUP_DIR = Cfg::DS . 'backups' . Cfg::DS;

    /**
     * Надо ли добавлять в дамп комментарии.
     *
     * @var bool
     */
    protected static $skipComments = true;

    /**
     * Формирование блока комментария.
     *
     * @param   string|array  $text  Текст комментария
     * @return  string
     */
    protected static function commentBlock($text = ''): string
    {
        if (
            self::$skipComments
            || empty($text)
            || (!is_array($text) && !is_string($text))
        ) {
            return '';
        }

        if (is_array($text)) {
            $text = implode(PHP_EOL . '-- ', $text);
        }

        return
            '-- ----------------------------' . PHP_EOL .
            '-- ' . $text . PHP_EOL .
            '-- ----------------------------' . PHP_EOL;
    }

    /**
     * Создание директории для дампов, закрытой для посетителей сайта.
     *
     * @return  string  Путь к директории
     */
    protected static function prepareDir(): string
    {
        FsDirectory::make(self::BACKUP_DIR);
        $accessFilePath = Cfg::DIRS_ROOT . self::BACKUP_DIR . Cfg::HTACCESS_FILE_NAME;

        if (!file_exists($accessFilePath)) {
            copy(Cfg::DIR_CMF_ROOT . Cfg::HTACCESS_FILE_NAME, $accessFilePath);
        }

        return Cfg::DIRS_ROOT . self::BACKUP_DIR;
    }

    /**
     * Сохранение файла с дампом.
     *
     * @param   string  $content  Данные для записи в файл
     * @return  string|FALSE      Относительный путь к созданному файлу или FALSE в случае ошибки
     */
    protected static function saveFile(string $content)
    {
        if (empty($content)) {
            return false;
        }

        $fileName = 'db-dump_' . Cfg::DB_BASENAME . '_' . Time::get('Y-m-d_H-i-s') . '.sql';
        $filePath = self::prepareDir() . $fileName;
        $zipPath = "{$filePath}.zip";

        return FsFile::make($filePath, $content)
        && FsArchive::makeZip([$filePath => $fileName], $zipPath, false, true)
            ? self::BACKUP_DIR . $fileName
            : false;
    }

    /**
     * Переключение флага, указывающего на необходимость
     * добавлять в дамп комментарии.
     *
     * @param  bool  $enable
     */
    public static function switchComments(bool $enable = true): void
    {
        self::$skipComments = !$enable;
    }

    /**
     * Создание дампа всех таблиц БД или только указанных.
     * Возвращает отчёт о выполненных действиях,
     * в том числе путь к полученному дампу в случае успеха.
     *
     * @param   string|array  $tables  Нужные таблицы через запятую или массивом. '*' - все таблицы БД.
     * @return  string
     */
    public static function doIt($tables = '*'): string
    {
        if (empty($tables)) {
            return 'Не выбрана ни одна таблица';
        }

        try {
            $log = '';
            $sql = self::commentBlock('CMF Sensei SQL Dump') . PHP_EOL;
            $sql .= 'SET foreign_key_checks = 0;' . PHP_EOL . PHP_EOL;

            if (
                $tables === '*'
                || (!is_array($tables) && !is_string($tables))
            ) {
                $tables = DBCommand::getTables();
            } else {
                $tables = is_array($tables) ? $tables : explode(',', $tables);
            }
            foreach ($tables as $key => $table) {
                $log .= "Backing up `$table`... ";
                $structure = DBCommand::query(
                    'SHOW CREATE TABLE ' . DBCommand::qC($table),
                    DBCommand::OUTPUT_FIRST_ROW
                );
                $sql .= self::commentBlock('Table structure for ' . $table)
                    . 'DROP TABLE IF EXISTS ' . DBCommand::qC($table) . ';' . PHP_EOL
                    . $structure['Create Table'] . ';' . PHP_EOL . PHP_EOL;

                $records = DBCommand::select(['from' => $table]);
                if (!empty($records)) {
                    $sql .= self::commentBlock('Records of ' . $table)
                        . DBQueryBuilder::insert($table, $records, true) . ';' . PHP_EOL . PHP_EOL;
                }
                $log .= 'Done!' . PHP_EOL;
            }
            $sql .= 'SET foreign_key_checks = 1;' . PHP_EOL;

            if ($file = self::saveFile($sql)) {
                $log .= PHP_EOL . 'Путь к файлу дампа:' . PHP_EOL . $file;
            } else {
                $log .= PHP_EOL . 'Возникла ошибка при сохранении файла дампа';
            }
        } catch (Throwable $Ex) {
            $log .= 'Failure!' . PHP_EOL;
            Logger::registerException($Ex);
        }

        return $log;
    }
}
