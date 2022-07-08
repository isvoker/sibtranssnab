<?php

class TextBlockNotFoundEx extends Exception implements SenseiExceptionInterface
{
	private $ident;

	public function __construct(string $ident = '')
	{
		if ($ident) {
			$this->ident = $ident;
		}
		parent::__construct('', 0);
	}

	public function getError(): string
	{
		return $this->ident
			? "Текстовый блок \"{$this->ident}\" не найден"
			: 'Текстовый блок не найден';
	}
}
