<?php
/**
 * Исключения, связанные с редактированием страницы сайта.
 *
 * @author Dmitriy Lunin
 */
class PageEditEx extends Exception implements SenseiExceptionInterface
{
	/** Возможные причины */
	public const IDENT_IS_NOT_UNIQUE = 1;

	private $reason;

	public function __construct(string $reason = '')
	{
		$this->reason = $reason;
		parent::__construct('', 0);
	}

	public function getError(): string
	{
		switch ($this->reason) {
			case self::IDENT_IS_NOT_UNIQUE:
				return 'Идентификатор не уникален';

			default:
				return 'Ошибка при редактировании страницы';
		}
	}
}
