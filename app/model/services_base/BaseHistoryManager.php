<?php

ClassLoader::loadClass('CHistoryMeta');
ClassLoader::loadClass('CHistory');

/**
 * Базовая реализация класса [[HistoryManager]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[HistoryManager]].
 *
 * @author Pavel Nuzhdin <pnzhdin@gmail.com>
 * @author Lunin Dmitriy
 **/
class BaseHistoryManager extends EntityManager
{
    /**
     * @see     EntityManager::baseToObjects()
     * @param   array          $dbRows
     * @param  ?ObjectOptions  $Options
     * @return  array
     */
    public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
    {
        return parent::baseToObjects($dbRows, 'CHistory', $Options);
    }

    /**
     * Генерация объекта: запись в истории с заданным описанием.
     *
     * @param   string  $description    Описание события
     * @param   bool    $isUserHistory  Событие связано с учётной записью пользователя?
     * @return  mixed   Запись в истории (object) или FALSE, если ведение истории отключено
     */
    protected static function makeHistory(string $description, bool $isUserHistory = false)
    {
        return new CHistory([
            'user_id' => User::id(),
            'user_name' => User::name(),
            'ip' => Request::getUserIP(),
            'time' => Time::toSQLDateTime(),
            'is_user_history' => $isUserHistory,
            'description' => $description
        ]);
    }

    /**
     * Добавление текущему пользователю запись в историю.
     *
     * @param   string  $description    Описание события
     * @param   bool    $isUserHistory  Событие связано с учётной записью пользователя?
     * @return  mixed   Добавленная запись (object) или FALSE, если ведение истории отключено
     */
    public static function addHistory(string $description, bool $isUserHistory = false)
    {
        if (!Cfg::HISTORY_IS_ON) {
            return false;
        }

        $History = self::makeHistory($description, $isUserHistory);

        return parent::add($History, true);
    }

    /**
     * Добавление записи в историю с описанием изменения полей объекта (old --> new).
     *
     * @param   string              $description    Описание события
     * @param   AbstractEntityMeta  $Meta           Метаинформация объектов
     * @param   AbstractEntity      $OldObj         Объект до обновления
     * @param   AbstractEntity      $NewObj         Объект после обновления
     * @param   bool                $isUserHistory  Событие связано с учётной записью пользователя?
     * @return  mixed               Добавленная запись (object), FALSE, если ведение истории отключено
     *                              или NULL, если изменений нет и запись не была добавлена
     */
    public static function makeFieldsHistory(
        string $description,
        AbstractEntityMeta $Meta,
        AbstractEntity $OldObj,
        AbstractEntity $NewObj,
        bool $isUserHistory = false
    ) {
        if (!Cfg::HISTORY_IS_ON) {
            return false;
        }

        $details = '';
        $oldValues = $OldObj->getFieldsForOutput();
        $newValues = $NewObj->getFieldsForOutput();
        foreach ($Meta->getFieldsInfo() as $field => $FieldInfo) {
            if ($FieldInfo->isIgnoredByHistory()) {
                continue;
            }

            $oldValue = $oldValues[ $field ];
            $newValue = $newValues[ $field ];

            if ($oldValue != $newValue) {
                $details .= $FieldInfo->getName() . " ({$oldValue} --> {$newValue})."
                    . Html::BR_REPLACEMENT;
            }
        }

        if ($details) {
            $description .= ':' . Html::BR_REPLACEMENT . $details;
            return self::addHistory($description, $isUserHistory);
        }

        return null;
    }

    /**
     * Поиск записей в истории.
     *
     * @param   array          $restricts      Параметры поиска
     * @param  ?FetchOptions   $FetchOptions   Параметры выборки :
     * ~~~
     *   bool   $count   = false
     *   mixed  $orderBy = []
     *   int    $limit   = null
     *   int    $offset  = 0
     * ~~~
     * @param  ?ObjectOptions  $ObjectOptions  Параметры создания объекта :
     * ~~~
     *   bool  $withExtraData  = false
     *   bool  $forOutput      = false
     *   bool  $skipValidation = false
     *   bool  $showSensitive  = false
     * ~~~
     * @return  array
     */
    protected static function findHistory(
        array $restricts = [],
        FetchOptions $FetchOptions = null,
        ObjectOptions $ObjectOptions = null
    ): array {
        if (!Cfg::HISTORY_IS_ON) {
            throw new FeatureNotAvailableEx( FeatureNotAvailableEx::HISTORY_IS_DISABLED );
        }

        is_null($FetchOptions)  && $FetchOptions = new FetchOptions();
        is_null($ObjectOptions) && $ObjectOptions = new ObjectOptions();

        //$join  = $options['join']  ?? null; // Доп. таблицы в совместимом с [[DBQueryBuilder::select()]] формате
        //$where = $options['where'] ?? 'TRUE';
        $join  = null;
        $where = 'TRUE';

        $tbl = CHistoryMeta::getDBTable();

        foreach ($restricts as $key => $value) {
            if (empty($value) || !is_string($value)) {
                continue;
            }

            switch ($key) {
                case 'query':
                    $valueQV = '\'%' . DBCommand::eV($value) . '%\'';
                    $where .= " AND ( {$tbl}.ip LIKE {$valueQV} OR "
                        . "UPPER({$tbl}.description) LIKE UPPER({$valueQV}) OR "
                        . "UPPER({$tbl}.user_name) LIKE UPPER({$valueQV}) )";
                    break;
                case 'user_id':
                    $where .= " AND {$tbl}.user_id = " . DBCommand::qV($value);
                    break;
                case 'time_min':
                    $where .= " AND {$tbl}.time >= " . DBCommand::qV(Time::toSQLDateTime($value));
                    break;
                case 'time_max':
                    $where .= " AND {$tbl}.time <= " . DBCommand::qV(Time::toSQLDateTime($value));
                    break;
                default:
                    break;
            }
        }

        $dbRows = DBCommand::select([
            'calc_found_rows' => $FetchOptions->getCount(),
            'select' => [$tbl => ['*']],
            'from'   => DBCommand::qC($tbl),
            'join'   => $join,
            'where'  => $where,
            'order'  => $FetchOptions->getOrderBy(),
            'limit'  => $FetchOptions->getLimit(),
            'offset' => $FetchOptions->getOffset()
        ]);

        if ($FetchOptions->getCount()) {
            DBCommand::calcFoundRows();
        }

        return self::toObjects($dbRows, $ObjectOptions);
    }

    /**
     * Возвращает список объектов в истории, связанных
     * с учётной записью пользователя, ID которого совпадает с $restricts['user_id'].
     *
     * @see     BaseHistoryManager::findHistory()
     * @param   array          $restricts
     * @param  ?FetchOptions   $FetchOptions
     * @param  ?ObjectOptions  $ObjectOptions
     * @return  array
     */
    public static function findUserHistory(
        array $restricts = [],
        FetchOptions $FetchOptions = null,
        ObjectOptions $ObjectOptions = null
    ): array {
        if (
            User::id() !== (int) $restricts['user_id']
            && !User::isAdmin()
        ) {
            throw new AccessDeniedEx();
        }

        return self::findHistory($restricts, $FetchOptions, $ObjectOptions);
    }
}
