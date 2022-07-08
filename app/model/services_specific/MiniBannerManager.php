<?php

ClassLoader::loadClass('CMiniBannerMeta');
ClassLoader::loadClass('CMiniBanner');

/**
 * Управление сущностями [[CMiniBanner]].
 *
 */
class MiniBannerManager extends EntityManager implements EntityManagerInterface
{
    /**
     * @see     [[EntityManager::baseToObjects()]]
     * @param   array
     * @param   ObjectOptions
     * @return  array
     */
    public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
    {
        return parent::baseToObjects($dbRows, 'CMiniBanner', $Options);
    }

    /**
     * @see     [[EntityManager::baseGetById()]]
     * @param   int
     * @param   ObjectOptions
     * @return  AbstractEntity
     */
    public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
    {
        return parent::baseGetById('CMiniBanner', '', $id, $Options);
    }

    /**
     * @see     [[EntityManager::baseFetch()]]
     * @param   FetchBy
     * @param   FetchOptions
     * @param   ObjectOptions
     * @return  array
     */
    public static function fetch(
        FetchBy $FetchBy = null,
        FetchOptions $FetchOptions = null,
        ObjectOptions $ObjectOptions = null
    ): array {
        return parent::baseFetch(CMiniBannerMeta::getInstance(), $FetchBy, $FetchOptions, $ObjectOptions);
    }
}
