<?php
/**
 * Базовая реализация класса [[CHistory]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[CHistory]]
 *
 * @author Pavel Nuzhdin <pnzhdin@gmail.com>
 * @author Dmitriy Lunin
 */
class BaseCHistory extends AbstractEntity
{
	/**
     * Добавление данных связанной учётной записи.
     */
	public function pullUser(): void
	{
		$User = UserManager::getById($this->user_id);
		$this->extraData['user'] = $User->getFields();
	}

	/** @see AbstractEntity::buildExtraData() */
	public function buildExtraData(): void
	{
		$this->pullUser();
	}
}
