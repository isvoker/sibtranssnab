<?php
/**
 * Исключения, возникающие при работе с файловой системой.
 *
 * @author Dmitriy Lunin
 */
class FilesystemEx extends Exception implements SenseiExceptionInterface
{
	/** Возможные причины */
	public const FILE_IS_NOT_READABLE = 1;
	public const IS_NOT_A_DIRECTORY = 2;
	public const DIRECTORY_IS_NOT_WRITABLE = 3;
	public const DIRECTORY_IS_NOT_CREATED = 4;
	public const DIRECTORY_IS_NOT_DELETED = 5;

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
			case self::FILE_IS_NOT_READABLE:
				return "Could not read file `{$path}`";

			case self::IS_NOT_A_DIRECTORY:
				return "`{$path}` is not a directory";

			case self::DIRECTORY_IS_NOT_WRITABLE:
				return "Directory `{$path}` is not writable";

			case self::DIRECTORY_IS_NOT_CREATED:
				return "Could not create directory `{$path}`";

			case self::DIRECTORY_IS_NOT_DELETED:
				return "Could not remove directory `{$path}`";

			default:
				return $this->reason ?: 'Filesystem error';
		}
	}
}
