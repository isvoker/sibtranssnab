<?php

class ImageInAlbumNotFoundEx extends Exception implements SenseiExceptionInterface
{
	public function getError(): string
	{
		return 'Изображение не найдено';
	}
}
