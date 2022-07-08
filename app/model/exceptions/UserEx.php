<?php
/**
 * Исключения, вызываемые при работе с аккаунтом пользователя.
 *
 * @author Dmitriy Lunin
 */
class UserEx extends Exception implements SenseiExceptionInterface
{
	/** Возможные причины */
	public const CREDENTIALS_IS_WRONG = 1;
	public const STATUS_IS_WRONG = 2;
	public const PASSWORD_IS_INVALID = 3;
	public const PASSWORD_IS_WEAK = 4;
	public const PASSWORD_REPEAT_DO_NOT_MATCH = 5;
	public const EMAIL_IS_INVALID = 6;
	public const LOGIN_ALREADY_IN_USE = 7;
	public const PHONE_ALREADY_IN_USE = 8;
	public const AGREEMENT_IS_NOT_ACCEPTED = 9;

	private $reason;
	private $comment;

	public function __construct($reason = null, $comment = null)
	{
		$this->reason = $reason;
		$this->comment = $comment;
		parent::__construct('', 0);
	}

	public function getError(): string
	{
		switch ($this->reason) {
			case self::CREDENTIALS_IS_WRONG:
				$msg = 'Неправильные реквизиты для входа.';
				break;

			case self::STATUS_IS_WRONG:
				$msg = 'Учётная запись заблокирована.';
				break;

			case self::PASSWORD_IS_INVALID:
				$msg = 'Неверный пароль.';
				break;

			case self::PASSWORD_IS_WEAK:
				$msg = 'Выбранный пароль не надёжен.';
				break;

			case self::PASSWORD_REPEAT_DO_NOT_MATCH:
				$msg = 'Новый пароль надо писать два раза без ошибок.';
				break;

			case self::EMAIL_IS_INVALID:
				$msg = 'Указан некорректный адрес электронной почты.';
				break;

			case self::LOGIN_ALREADY_IN_USE:
				$msg = 'Такой логин уже занят. Выберите другой.';
				break;

			case self::PHONE_ALREADY_IN_USE:
				$msg = 'Такой телефон уже используется другим пользователем. Выберите другой.';
				break;

			case self::AGREEMENT_IS_NOT_ACCEPTED:
				$msg = 'Не получено соглашение с правилами.';
				break;

			default:
				$msg = 'Авторизация не удалась.';
		}

		if ($this->comment) {
			$msg .= ' ' . $this->comment;
		}

		return $msg;
	}
}