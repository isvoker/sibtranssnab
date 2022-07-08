<?php
/**
 * @deprecated
 */
class UserPasswordEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные причины */
    const INVALID = 1;
    const WEAK = 2;
    const DO_NOT_MATCH = 3;

    private $reason;

    public function __construct(string $reason = '')
    {
        $this->reason = $reason;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::WEAK:
                return 'Выбранный пароль не надёжен';
            case self::DO_NOT_MATCH:
                return 'Новый пароль надо писать два раза без ошибок';
            case self::INVALID:
            default:
                return 'Неверный пароль';
        }
    }
}
