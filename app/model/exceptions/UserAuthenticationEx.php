<?php
/**
 * @deprecated
 */
class UserAuthenticationEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные ошибки аутентификации */
    const WRONG_LOGIN = 1;
    const WRONG_PASS = 2;
    const WRONG_STATUS = 3;

    private $reason;
    private $comment;

    public function __construct($reason = null, $comment = null)
    {
        $this->reason = $reason;
        $this->comment = $comment;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::WRONG_LOGIN:
            case self::WRONG_PASS:
                $msg = 'Неправильный логин или пароль';
                break;
            case self::WRONG_STATUS:
                $msg = 'Учётная запись заблокирована';
                break;
            default:
                $msg = 'Авторизация не удалась';
        }

        if ($this->comment) {
            $msg .= '. ' . $this->comment;
        }

        return $msg;
    }
}
