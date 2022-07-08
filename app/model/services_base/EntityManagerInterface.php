<?php
/**
 * Обязательные методы, необходимые для управления сущностями.
 *
 * @author Lunin Dmitriy
 */
interface EntityManagerInterface
{
    /**
     * @see     EntityManager::baseToObjects()
     * @param   array          $dbRows
     * @param  ?ObjectOptions  $Options
     * @return  array
     */
    public static function toObjects(array $dbRows, ObjectOptions $Options = null): array;

    /**
     * @see     EntityManager::baseGetById()
     * @param   int            $id
     * @param  ?ObjectOptions  $Options
     * @return  AbstractEntity
     */
    public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity;

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
    ): array;
}
