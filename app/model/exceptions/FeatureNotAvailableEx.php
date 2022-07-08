<?php
/**
 * Исключения, связанные с недоступностью какой-либо функциональности.
 *
 * @author Dmitriy Lunin
 */
class FeatureNotAvailableEx extends Exception implements SenseiExceptionInterface
{
	/** Возможные причины */
	public const DELETING_USERS_IS_DISABLED = 1;
	public const HISTORY_IS_DISABLED = 2;

	private $reason;

	public function __construct(string $reason = '')
	{
		$this->reason = $reason;
		parent::__construct('', 0);
	}

	public function getError(): string
	{
		switch ($this->reason) {
			case self::DELETING_USERS_IS_DISABLED:
				return 'Удаление пользователей отключено';

			case self::HISTORY_IS_DISABLED:
				return 'Ведение истории отключено';

			default:
				return $this->reason ?: 'Функциональность не доступна';
		}
	}
}
