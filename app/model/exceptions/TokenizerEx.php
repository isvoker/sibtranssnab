<?php
/**
 * Исключения, связанные с обработкой проверочных токенов.
 *
 * @author Dmitriy Lunin
 */
class TokenizerEx extends Exception implements SenseiExceptionInterface
{
	/** Возможные причины */
	public const TOKEN_IS_INVALID = 1;
	public const SIGN_IS_INVALID = 2;
	public const TOKEN_HAS_EXPIRED = 3;

	private $reason;

	public function __construct(string $reason = '')
	{
		$this->reason = $reason;
		parent::__construct('', 0);
	}

	public function getError(): string
	{
		switch ($this->reason) {
			case self::TOKEN_IS_INVALID:
				return 'Некорректный токен';

			case self::SIGN_IS_INVALID:
				return 'Подпись не действительна';

			case self::TOKEN_HAS_EXPIRED:
				return 'Срок действия токена истёк';

			default:
				return 'Токен не действителен';
		}
	}
}
