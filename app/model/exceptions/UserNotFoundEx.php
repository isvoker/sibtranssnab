<?php

class UserNotFoundEx extends Exception implements SenseiExceptionInterface
{
    public function getError(): string
    {
        return 'Пользователь не найден';
    }
}
