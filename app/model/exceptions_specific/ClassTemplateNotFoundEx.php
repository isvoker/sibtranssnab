<?php

class ClassTemplateNotFoundEx extends Exception implements SenseiExceptionInterface
{
	public function getError(): string
	{
		return '[Объект не найден]';
	}
}
