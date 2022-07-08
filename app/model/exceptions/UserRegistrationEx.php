<?php
/**
 * @deprecated
 */
class UserRegistrationEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные причины */
    const LOGIN_ALREADY_IN_USE = 1;
    const EMAIL_IS_INVALID = 2;
    const PHONE_ALREADY_IN_USE = 3;
    const YOU_DO_NOT_AGREE = 4;

    private $reason;

    public function __construct(string $reason = '')
    {
        $this->reason = $reason;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::LOGIN_ALREADY_IN_USE:
                return 'Такой логин уже занят. Выберите другой.';
            case self::EMAIL_IS_INVALID:
                return 'Указан некорректный адрес электронной почты';
            case self::PHONE_ALREADY_IN_USE:
                return 'Такой телефон уже используется другим пользователем. Выберите другой.';
            case self::YOU_DO_NOT_AGREE:
                return 'Сначала следует прочитать правила и согласиться с ними';
            default:
                return 'Регистрация пользователя не удалась';
        }
    }
}
