<?php

ClassLoader::preloadClass('services_base/BasePageManager');

/**
 * Управление объектами класса [Страница сайта].
 *
 * @author Lunin Dmitriy
 */
class PageManager extends BasePageManager {
    /**
     * Поиск страницы первого уровня (дочерней от главной) относительно страницы с указанным ID
     *
     * @param   int  $id
     * @return  int
     */
    public static function getFirstLevelParentPageId(int $id): int
    {
        if (empty($id)) {
            return 0;
        }

        $page = DBCommand::select([
            'select' => 'id, parent',
            'from' => CPageMeta::getDBTable(),
            'where' => 'id=' . DBCommand::qV($id)
        ], DBCommand::OUTPUT_FIRST_ROW);

        if (empty($page)) {
            return 0;
        }

        $CycleIteration = 0;

        while ($page['parent'] != Cfg::PAGE_ID_FRONT) {
            if ($CycleIteration > 10) {
                break;
            }

            $page = DBCommand::doSelect([
                'select' => 'id, parent',
                'from' => CPageMeta::getDBTable(),
                'where' => 'id=' . DBCommand::qV($page['parent'])
            ], DBCommand::OUTPUT_FIRST_ROW);

            if (empty($page['parent'])) {
                return 0;
            }
        }

        if (!empty($page['id'])) {
            return (int)$page['id'];
        }

        return 0;
    }
}
