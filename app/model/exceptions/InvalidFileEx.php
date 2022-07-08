<?php
/**
 * Исключения, вызванные некорректным входным файлом.
 *
 * @author Dmitriy Lunin
 */
class InvalidFileEx extends Exception implements SenseiExceptionInterface
{
	/** Возможные причины */
	public const TYPE_IS_INVALID = 1;
	public const DATA_IS_INVALID = 2;
	public const IS_NOT_DBF = 3;
	public const IS_NOT_XLS = 4;
	public const IS_NOT_XLSX = 5;

	private $reason;
	private $path;

	public function __construct(string $reason = '', string $path = '')
	{
		$this->reason = $reason;
		$this->path = $path;
		parent::__construct($reason, 0);
	}

	public function getError(): string
	{
		$path = $this->path ? Html::qSC($this->path) : '[unknown]';

		switch ($this->reason) {
			case self::TYPE_IS_INVALID:
				return "Формат файла `{$path}` не поддерживается";

			case self::DATA_IS_INVALID:
				return "Файл `{$path}` повреждён";

			case self::IS_NOT_DBF:
				return "`{$path}` не является файлов формата \"XBase DataBase\"";

			case self::IS_NOT_XLS;
				return "`{$path}` не является файлов формата \"Microsoft Excel (97/2003)\"";

			case self::IS_NOT_XLSX:
				return "`{$path}` не является файлов формата \"Excel Microsoft Office Open XML\"";

			default:
				return $this->reason ?: 'File is not valid';
		}
	}
}
