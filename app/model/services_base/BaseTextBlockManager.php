<?php

ClassLoader::loadClass('CTextBlockMeta');
ClassLoader::loadClass('CTextBlock');
ClassLoader::loadClass('TextBlockNotFoundEx');

/**
 * Базовая реализация класса [[TextBlockManager]]
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[TextBlockManager]].
 *
 * @author Lunin Dmitriy
 */
class BaseTextBlockManager extends EntityManager implements EntityManagerInterface
{
    /**
     * @see     EntityManager::baseToObjects()
     * @param   array          $dbRows
     * @param  ?ObjectOptions  $Options
     * @return  array
     */
    public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
    {
        return parent::baseToObjects($dbRows, 'CTextBlock', $Options);
    }

    /**
     * @see     EntityManager::baseGetById()
     * @param   int            $id
     * @param  ?ObjectOptions  $Options
     * @return  CTextBlock
     */
    public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
    {
        return parent::baseGetById('CTextBlock', 'TextBlockNotFoundEx', $id, $Options);
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
            CTextBlockMeta::getInstance(),
            $FetchBy,
            $FetchOptions,
            $ObjectOptions
        );
    }

    /**
     * Получение содержимого текстового блока
     *
     * @param   string  $ident              Идентификатор запрашиваемого блока
     * @param   bool    $createIfNotExists  Создать блок в случае отсутствия его в БД
     * @param   bool    $withButtons        Добавить в начало код кнопок редактирования блока
     * @return  string
     */
    public static function getContent(
        string $ident,
        bool $createIfNotExists = true,
        bool $withButtons = true
    ): string {
        $html = '';

        $dbTable = CTextBlockMeta::getDBTable();
        $block = DBCommand::select([
            'select' => [['id', 'content']],
            'from'   => DBCommand::qC($dbTable),
            'where'  => [['clause' => ':ident: = {ident}', 'values' => [$ident]]]
        ], DBCommand::OUTPUT_FIRST_ROW);

        if (empty($block)) {
            if ($createIfNotExists) {
                $block['id'] = DBCommand::insert($dbTable, ['ident' => $ident]);
            } else {
                throw new TextBlockNotFoundEx($ident);
            }
        } elseif ($block['content']) {
            $html = Html::strip(Html::dSC($block['content']));
        }

        if ($withButtons) {
            $html = Html::entityEditButtons(
                'CTextBlock',
                [Action::UPDATE],
                ['id' => $block['id'], 'userGroups' => CTextBlockMeta::getPermissions(Action::UPDATE)]
            )
            . $html;
        }

        return $html;
    }
}
