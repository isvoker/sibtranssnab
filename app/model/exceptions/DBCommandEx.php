<?php
/**
 * Исключения, связанные с работой с СУБД.
 *
 * @author Dmitriy Lunin
 */
class DBCommandEx extends Exception implements SenseiExceptionInterface
{
    /** Возможные причины */
    public const CONNECTION = 1;
    public const SYNTAX = 2;

    private $reason;

    public function __construct(string $reason = '')
    {
        $this->reason = $reason;
        parent::__construct($reason, 0);
    }

    public function getError(): string
    {
        switch ($this->reason) {
            case self::CONNECTION:
                return 'Connection to the DB is not established';

            case self::SYNTAX:
                return 'Error in SQL syntax';

            default:
                return $this->reason ?: 'Database Exception';
        }
    }
}
