<?php
/**
 * SimpleXMLElement с новыми полезными возможностями
 *
 * @author Dmitriy Lunin
 */
class LessSimpleXMLElement extends SimpleXMLElement
{
	public function __isset(string $name): bool
	{
		return $this->$name !== null;
	}

	/**
	 * Получение строкового значения элемента
	 *
	 * @param   string  $name  Имя элемента
	 * @return  string
	 */
	public function getString(string $name): string
	{
		return $this->$name;
	}

	/**
	 * Получение строкового значения атрибута элемента
	 *
	 * @param   string  $name  Имя атрибута
	 * @return  string
	 */
	public function getAttr(string $name): string
	{
		return $this[$name];
	}
}
