<?php
/**
 * Исключения, возникающие при загрузке файлов в систему.
 *
 * @author Dmitriy Lunin
 */
class FilesUploadEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные причины */
    public const NO_FILES = 1;
    public const SIZE_IS_INVALID = 2;
    public const COUNT_LIMIT = 3;
    public const NAME_IS_INVALID = 4;

    private $reason;
    private $params;

    public function __construct(string $reason = '', $params = null) {
        $this->reason = $reason;
        $this->params = $params;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::NO_FILES:
                return 'Вы забыли выбрать файл';

            case self::SIZE_IS_INVALID:
                return $this->params
                    ? "Файлы размером более {$this->params} B загрузить не получится"
                    : 'Размер загружаемого файла превысил допустимое значение';

            case self::COUNT_LIMIT:
                return "Вам разрешено загружать не более {$this->params} файлов";

            case self::NAME_IS_INVALID:
                return 'Загружаемый файл должен иметь определённое имя. Вероятно, Вы выбрали не тот файл.';

            default:
                return 'Ошибка при загрузке файла';
        }
    }
}
