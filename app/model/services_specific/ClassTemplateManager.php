<?php

ClassLoader::loadClass('CClassTemplateMeta');
ClassLoader::loadClass('CClassTemplate');
ClassLoader::loadClass('ClassTemplateNotFoundEx');

/**
 * Управление объектами класса [[CClassTemplate]].
 *
 * @author Lunin Dmitriy
 */
class ClassTemplateManager extends EntityManager implements EntityManagerInterface
{
	/**
	 * @see     EntityManager::baseToObjects()
	 * @param   array          $dbRows
	 * @param  ?ObjectOptions  $Options
	 * @return  array
	 */
	public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
	{
		return parent::baseToObjects($dbRows, 'CClassTemplate', $Options);
	}

	/**
	 * @see     EntityManager::baseGetById()
	 * @param   int            $id
	 * @param  ?ObjectOptions  $Options
	 * @return  CClassTemplate
	 */
	public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
	{
		return parent::baseGetById('CClassTemplate', 'ClassTemplateNotFoundEx', $id, $Options);
	}

	/**
	 * @see     EntityManager::baseFetch()
	 * @param  ?FetchBy        $FetchBy
	 * @param  ?FetchOptions   $FetchOptions
	 * @param  ?ObjectOptions  $ObjectOptions
	 * @return  array
	 */
	public static function fetch(
		FetchBy $FetchBy = null,
		FetchOptions $FetchOptions = null,
		ObjectOptions $ObjectOptions = null
	): array {
		return parent::baseFetch(
			CClassTemplateMeta::getInstance(),
			$FetchBy,
			$FetchOptions,
			$ObjectOptions
		);
	}
}
