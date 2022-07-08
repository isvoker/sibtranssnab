<?php
/**
 * Исключения, связанные с логикой разграничения прав доступа.
 *
 * @author Dmitriy Lunin
 */
class AccessDeniedEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные причины */
    public const YOU_CANT = 1;

    private $reason;

    public function __construct(string $reason = '')
    {
        $this->reason = $reason;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::YOU_CANT:
                return 'У вас нет разрешения на данную операцию';

            default:
                return 'Доступ запрещён';
        }
    }
}
