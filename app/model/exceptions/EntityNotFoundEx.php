<?php

class EntityNotFoundEx extends Exception implements SenseiExceptionInterface
{
    public function getError(): string
    {
        return 'Искомая запись не найдена';
    }
}
