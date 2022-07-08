<?php
/**
 * Исключения, вызванные ошибкой внутренней логики.
 *
 * @author Dmitriy Lunin
 */
class InternalEx extends Exception implements SenseiExceptionInterface
{
	/** Возможные причины */
	public const CLASS_NOT_DEFINED_IN_MAP = 1;
	public const RESOURCE_NOT_DEFINED_IN_MAP = 2;

	private $reason;
	private $params;

	public function __construct(string $reason = '', string $params = '')
	{
		$this->reason = $reason;
		$this->params = $params;
		parent::__construct($reason, 0);
	}

	public function getError(): string
	{
		switch ($this->reason) {
			case self::CLASS_NOT_DEFINED_IN_MAP:
				return "Класс `{$this->params}` не зарегистрирован";

			case self::RESOURCE_NOT_DEFINED_IN_MAP:
				return "Ресурс `{$this->params}` не зарегистрирован";

			default:
				return $this->reason ?: 'Внутренняя ошибка';
		}
	}
}
