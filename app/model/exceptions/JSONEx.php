<?php
/**
 * Исключения, связанные с кодированием/декодированием JSON.
 *
 * @author Dmitriy Lunin
 */
class JSONEx extends Exception implements SenseiExceptionInterface
{
	protected $errorCode;

	public function __construct($errorCode = 0) {
		$this->errorCode = $errorCode;
		parent::__construct('', 0);
	}

	public function getError(): string
	{
		switch ($this->errorCode) {
			case JSON_ERROR_DEPTH:
				$errorMsg = 'Достигнута максимальная глубина стека';
				break;

			case JSON_ERROR_STATE_MISMATCH:
				$errorMsg = 'Неверный или не корректный JSON';
				break;

			case JSON_ERROR_CTRL_CHAR:
				$errorMsg = 'Ошибка управляющего символа, возможно неверная кодировка';
				break;

			case JSON_ERROR_SYNTAX:
				$errorMsg = 'Синтаксическая ошибка';
				break;

			case JSON_ERROR_UTF8:
				$errorMsg = 'Некорректные символы UTF-8, возможно неверная кодировка';
				break;

			case JSON_ERROR_RECURSION:
				$errorMsg = 'Одна или несколько зацикленных ссылок в кодируемом значении';
				break;

			case JSON_ERROR_INF_OR_NAN:
				$errorMsg = 'Одно или несколько значений NAN или INF в кодируемом значении';
				break;

			case JSON_ERROR_UNSUPPORTED_TYPE:
				$errorMsg = 'Передано значение с неподдерживаемым типом';
				break;

			default:
				$errorMsg = 'Неизвестная ошибка';
				break;
		}

		return 'JSON Error: ' . $errorMsg;
	}
}
