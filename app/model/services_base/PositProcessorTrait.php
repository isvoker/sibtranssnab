<?php
/**
 * Поддержка автоматической установки значения поля 'posit' объекта
 *
 * @author Dmitriy Lunin
 */
trait PositProcessorTrait
{
    /**
     * Корректировка поля 'posit' переданного объекта и,
     * при необходимости, у других записей в той же таблице.
     *
     * Новую позицию можно указать относительно некоторой другой записи
     * в таблице ($Object->extraData['posit_target']) с заданием положения ($Object->extraData['posit_point']):
     * 'prepend' (в начале), 'append' (в конце), 'top' (перед) или 'bottom' (после).
     * По умолчанию объект "ставится" к конец.
     *
     * @param  AbstractEntity  $Object
     */
    public static function setPosit(AbstractEntity $Object): void
    {
        if (isset($Object->extraData['posit_point'])) {
            if (isset($Object->extraData['posit_target'])) {
                self::setRelativePosit($Object);
            } elseif ($Object->extraData['posit_point'] === 'prepend') {
                self::setFirstPosit($Object);
            } else {
                self::setLastPosit($Object);
            }
        } else {
            self::setLastPosit($Object);
        }
    }

    /**
     * У переданного объекта поле 'posit' вычисляется относительно $Object->extraData['posit_target'],
     * другие записи в той же таблице соответственно "сдвигаются".
     *
     * @param  AbstractEntity  $Object
     */
    protected static function setRelativePosit(AbstractEntity $Object): void
    {
        $Target = self::getById($Object->extraData['posit_target']);
        $point = $Object->extraData['posit_point'];

        if ($Object->fieldExists('parent')) {
            $Object->parent = $point === 'append' ? $Target->id : $Target->parent;
            $where = [[
                'oper' => 'AND',
                'clause' => ':parent: = {parent}',
                'values' => [$Target->parent]
            ]];
        } else {
            $where = [];
        }

        if ($point === 'append') {
            self::setLastPosit($Object);
            return;
        }

        $oldPosit = $Object->posit ?: DBQueryBuilder::MAX_BIGINT_UNSIGNED;
        $Object->posit = $Target->posit;
        if ($oldPosit < $Target->posit) {
            $change = '-';
            $targetCond = '<';
            $cond = '>';
            if ($point === 'top') {
                --$Object->posit;
            } else {
                $targetCond .= '=';
            }
        } else {
            $change = '+';
            $targetCond = '>';
            $cond = '<';
            if ($point === 'bottom') {
                ++$Object->posit;
            } else {
                $targetCond .= '=';
            }
        }
        $Object->mustTrust('posit');

        $where[] = [
            'oper' => 'AND',
            'clause' => ":posit: {$targetCond} {value}",
            'values' => [$Target->posit]
        ];
        $where[] = [
            'oper' => 'AND',
            'clause' => ":posit: {$cond} {value}",
            'values' => [$oldPosit]
        ];

        DBCommand::update(
            $Object->getMetaInfo()->getDBTable(),
            ['posit' => "= posit {$change} 1"],
            $where
        );
    }

    /**
     * У переданного объекта поле 'posit' принимает значение '1',
     * а у других записей в той же таблице увеличивается на '1'.
     *
     * @param  AbstractEntity  $Object
     */
    protected static function setFirstPosit(AbstractEntity $Object): void
    {
        DBCommand::update(
            $Object->getMetaInfo()->getDBTable(),
            ['posit' => '= posit + 1'],
            $Object->fieldExists('parent') ? 'parent = ' . DBCommand::qV($Object->parent) : ''
        );

        $Object->setTrust('posit', 1);
    }

    /**
     * У переданного объекта поле 'posit' принимает следующее за максимальным
     * среди имеющихся записей в той же таблице значение.
     *
     * @param  AbstractEntity  $Object
     */
    protected static function setLastPosit(AbstractEntity $Object): void
    {
        $query = [
            'select' => 'MAX(posit)',
            'from' => DBCommand::qC( $Object->getMetaInfo()->getDBTable() )
        ];
        if ($Object->fieldExists('parent')) {
            $query['where'] = 'parent = ' . DBCommand::qV($Object->parent);
        }

        $Object->setTrust('posit', DBCommand::select($query, DBCommand::OUTPUT_FIRST_CELL) + 1);
    }
}
