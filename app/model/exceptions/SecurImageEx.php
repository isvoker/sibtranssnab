<?php

class SecurImageEx extends Exception implements SenseiExceptionInterface
{
	public function getError(): string
	{
		return 'Введён неверный проверочный код';
	}
}
