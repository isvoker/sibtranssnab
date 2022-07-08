<?php

ClassLoader::loadClass('CBlockMeta');
ClassLoader::loadClass('CBlock');
ClassLoader::loadClass('BlockNotFoundEx');

/**
 * Базовая реализация класса [[BlockManager]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[BlockManager]].
 *
 * @author Lunin Dmitriy
 */
class BaseBlockManager extends EntityManager implements EntityManagerInterface
{
	/**
     * @see     EntityManager::baseToObjects()
	 * @param   array          $dbRows
	 * @param  ?ObjectOptions  $Options
	 * @return  array
	 */
	public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
	{
		return parent::baseToObjects($dbRows, 'CBlock', $Options);
	}

	/**
     * @see     EntityManager::baseGetById()
     * @param   int            $id
     * @param  ?ObjectOptions  $Options
     * @return  CBlock
	 */
	public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
	{
		return parent::baseGetById('CBlock', 'BlockNotFoundEx', $id, $Options);
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
		    CBlockMeta::getInstance(),
            $FetchBy,
            $FetchOptions,
            $ObjectOptions
        );
	}

	/**
	 * Получение данных о модуле и параметрах блока.
	 *
	 * @param   string  $ident  Идентификатор запрашиваемого блока
	 * @return  array
	 */
	public static function getBlockData(string $ident): array
	{
		$block = DBCommand::select([
			'select' => [['module', 'file', 'props']],
			'from'   => DBCommand::qC( CBlockMeta::getDBTable() ),
			'where'  => DBCommand::qC('ident') . ' = ' . DBCommand::qV($ident)
		], DBCommand::OUTPUT_FIRST_ROW);

		if (empty($block)) {
			throw new BlockNotFoundEx($ident);
		}

		return $block;
	}
}
