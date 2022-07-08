<?php
/**
 * Поддержка статусов для классов метаинформации сущностей.
 *
 * @author Dmitriy Lunin
 */
trait StatusesMetaTrait
{
    /** Коды статусов, простые числа */
    //const STATUS_CODES = [
    //	'status1' => 2,
    //	'status2' => 3
    //];

    /** Описание статусов */
    //const STATUS_DESCRIPTIONS = [
    //	self::STATUS_CODES['status1'] => '',
    //	self::STATUS_CODES['status2'] => ''
    //];

    /**
     * Получение кодов всех возможных статусов.
     *
     * @return array
     */
    final public static function getStatuses(): array
    {
        return static::STATUS_CODES;
    }

    /**
     * Получение кода статуса по его обозначению.
     *
     * @param   string  $status  Статус
     * @return  int
     */
    final public static function getStatusCode(string $status): int
    {
        return static::STATUS_CODES[ $status ] ?? 0;
    }

    /**
     * Получение описаний всех возможных статусов.
     *
     * @return array
     */
    final public static function getDescriptions(): array
    {
        return static::STATUS_DESCRIPTIONS;
    }

    /**
     * Получение описания статуса по его коду.
     *
     * @param   int  $code  Код статуса
     * @return  string
     */
    final public static function getStatusDescription(int $code): string
    {
        return static::STATUS_DESCRIPTIONS[$code] ?? '';
    }
}
