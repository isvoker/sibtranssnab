<?php
/**
 * Исключения, связанные с редактированием сущности.
 *
 * @author Dmitriy Lunin
 */
class EntityEditEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные причины */
    public const ACCESS_DENIED = 1;
    public const FIELD_IS_EMPTY = 2;
    public const FIELD_IS_BAD = 3;
    public const UK_FIELD_IS_EMPTY = 4;
    public const UK_IS_EMPTY = 5;

    private $reason;
    private $params;

    public function __construct(string $reason = '', string $params = '') {
        $this->reason = $reason;
        $this->params = $params;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::ACCESS_DENIED:
                return "У Вас недостаточно прав для редактирования поля `{$this->params}`";

            case self::FIELD_IS_EMPTY:
                return "Не заполнено поле `{$this->params}`";

            case self::FIELD_IS_BAD:
                return "Неверный формат данных поля `{$this->params}`";

            case self::UK_FIELD_IS_EMPTY:
                return "Field `{$this->params}` from unique key is empty";

            case self::UK_IS_EMPTY:
                return 'Unique key constraints is empty';

            default:
                return 'Ошибка при редактировании записи';
        }
    }
}
