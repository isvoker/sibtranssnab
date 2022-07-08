<?php

ClassLoader::loadClass('PageManager');

/**
 * Статичный класс MenuBuilder.
 *
 * Набор методов для формирования меню сайта.
 *
 * @author Dmitriy Lunin
 */
class MenuBuilder
{
	/**
	 * Формирование иерархической структуры меню.
	 *
	 * @param  array  $menu      Элементы меню первого уровня
	 * @param  array  $subItems  Элементы вложенных меню
	 */
    protected static function placeSubItems(array &$menu, array &$subItems): void
	{
		foreach ($subItems as $parent => $items) {
			if (isset($menu[ $parent ])) {
                $menu[ $parent ]['sub_menu'] = empty($menu[ $parent ]['sub_menu'])
					? $items
					: array_merge($menu[ $parent ]['sub_menu'], $items);
				unset($subItems[ $parent ]);
			}
		}

		foreach ($menu as $id => &$item) {
			if (empty($subItems)) {
				return;
			}
			if (isset($item['sub_menu'])) {
				self::placeSubItems($item['sub_menu'], $subItems);
			}
		}
	}

    /**
     * Получение списка Страниц сайта для формирования меню.
     *
     * @param   mixed  $rootPage   Если задано, возвращены будут только страницы, которые
     *                             являются потомками страницы с таким ID или full_path
     * @param   bool   $recursive  При заданном $rootPage искать всех его потомков ?: только прямых
     * @return  array
     */
    public static function getPages($rootPage = null, bool $recursive = false): array
    {
        $pTable = CPageMeta::getDBTable();
        $pmTable = CPageMeta::getDBTablePermissions();

        $where = "{$pTable}.in_menu = 1";

        if (!User::isAdmin()) {
            $where .= " AND {$pTable}.is_hidden = 0";
        }

        if ($rootPage) {
            if ($recursive) {

                $where .= " AND {$pTable}.full_path LIKE ";

                if (is_numeric($rootPage)) {
                    $rootPath = DBCommand::select([
                        'select' => 'full_path',
                        'from'   => $pTable,
                        'where'  => 'id = ' . DBCommand::qV($rootPage)
                    ], DBCommand::OUTPUT_FIRST_CELL);
                } else {
                    $rootPath = $rootPage;
                }

                if ($rootPath && is_string($rootPath)) {
                    $rootPath = DBCommand::eV($rootPath);
                    $where .= "'{$rootPath}_%'";
                } else {
                    throw new InvalidArgumentException('Root page is invalid');
                }

            } else {

                $where .= " AND {$pTable}.parent = ";

                if (is_numeric($rootPage)) {
                    $where .= DBCommand::qV((int) $rootPage);
                } elseif (is_string($rootPage)) {
                    $where .= '(' . DBQueryBuilder::select([
                            'select' => 'id',
                            'from'   => $pTable,
                            'where'  => 'full_path = ' . DBCommand::qV($rootPage)
                        ]) . ')';
                } else {
                    throw new InvalidArgumentException('Root page is invalid');
                }

            }
        }

        if ($grpIdsStr = User::getEntity()->getPrivateExtraData('grp_ids_str')) {
            $join = "LEFT JOIN {$pmTable} ON {$pTable}.id = {$pmTable}.page_id";
            $where .= " AND ( ({$pTable}.is_public = 1) OR "
                . "({$pmTable}.group_id IN ({$grpIdsStr}) AND MOD({$pmTable}.statuses, " . CPageMeta::PERMS['reed'] . ') = 0) )';
        } else {
            $join = '';
            $where .= " AND {$pTable}.is_public = 1";
        }

        return DBCommand::select([
            'select' => [$pTable => ['id', 'parent', 'name', 'ident', 'full_path', 'module', 'not_clickable', 'direct_link']],
            'from'   => [$pTable => $pTable],
            'join'   => $join,
            'where'  => $where,
            'group'  => "{$pTable}.id",
            'order'  => "{$pTable}.parent, {$pTable}.posit"
        ]);
    }

    /**
     * Получение иерархической структуры меню из Страниц сайта.
     *
     * @param   mixed  $rootPage   Если задано, в меню попадут только страницы, которые
     *                             являются потомками страницы с таким ID или full_path
     * @param   bool   $recursive  При заданном $rootPage искать всех его потомков ?: только прямых
     * @return  array
     */
    public static function getItems($rootPage = null, bool $recursive = false): array
    {
        $currentURL = mb_strtolower( Request::getRelativeURL() );
        $pages = self::getPages($rootPage, $recursive);
        $menu = $subItems = [];
        $rootId = $rootPath = null;
        $rootPathLen = 0;

        if (!$rootPage) {
            $rootId = Cfg::PAGE_ID_FRONT;
        } elseif (is_numeric($rootPage)) {
            $rootId = (int) $rootPage;
        } elseif (is_string($rootPage)) {
            $rootPath = $rootPage;
            $rootPathLen = strlen($rootPath);
        } else {
            throw new InvalidArgumentException('Root page is invalid');
        }

        foreach ($pages as $page) {
            $item = [
                'id'   => $page['id'],
                'name' => Html::qSC($page['name']),
                'href' => $page['direct_link'] ?: $page['full_path']
            ];
            $item['active'] = ($item['href'] !== '/') && strpos($currentURL, $item['href']) === 0;

	        if ($page['not_clickable']) {
		        $item['href'] = '';
	        }

            if (
                (
                    $rootId
                    && (int) $page['parent'] === $rootId
                )
                || (
                    $rootPathLen
                    && substr_count(substr($page['full_path'], $rootPathLen), '/') === 1
                )
            ) {
                $menu[ $item['id'] ] = $item;
            } elseif (isset($menu[ $page['parent'] ])) {
                $menu[ $page['parent'] ]['sub_menu'][ $item['id'] ] = $item;
            } else {
                $subItems[ $page['parent'] ][ $item['id'] ] = $item;
            }
        }

        self::placeSubItems($menu, $subItems);

        return $menu;
    }
}
