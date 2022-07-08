<?php

class RequiredFieldIsEmptyEx extends Exception implements SenseiExceptionInterface
{
    private $field;

    public function __construct(string $field = '')
    {
        $this->field = $field;
        parent::__construct('', 0);
    }

    public function getError(): string
    {
        return $this->field
            ? 'Пожалуйста, укажите ' . $this->field
            : 'Все обязательные поля должны быть заполнены';
    }
}
