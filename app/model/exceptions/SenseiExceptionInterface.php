<?php
/**
 * Интерфейс исключений.
 *
 * @author Dmitriy Lunin
 */
interface SenseiExceptionInterface
{
	/** Получение информации об исключении */
	public function getError(): string;
}
