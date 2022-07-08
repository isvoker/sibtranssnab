<?php

class AlbumNotFoundEx extends Exception implements SenseiExceptionInterface
{
	public function getError(): string
	{
		return 'Альбом не найден';
	}
}
