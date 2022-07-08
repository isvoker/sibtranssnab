<?php

ClassLoader::loadClass('CPageMeta');
ClassLoader::loadClass('CPage');

/**
 * Базовая реализация класса [[PageManager]].
 *
 * Не меняйте и не используйте напрямую этот класс! Для этих целей есть [[PageManager]].
 *
 * @author Lunin Dmitriy
 */
class BasePageManager extends EntityManager implements EntityManagerInterface
{
    use PositProcessorTrait;
    use UniqueFieldsProcessorTrait;

    /**
     * @see     EntityManager::baseToObjects()
     * @param   array          $dbRows
     * @param  ?ObjectOptions  $Options
     * @return  array
     */
    public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
    {
        return parent::baseToObjects($dbRows, 'CPage', $Options);
    }

    /**
     * @see     EntityManager::baseGetById()
     * @param   int            $id
     * @param  ?ObjectOptions  $Options
     * @return  CPage
     */
    public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
    {
        return parent::baseGetById('CPage', '', $id, $Options);
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
        if (!CPageMeta::canIDoThis(Action::UPDATE)) {
            throw new AccessDeniedEx();
        }

        return parent::baseFetch(
            CPageMeta::getInstance(),
            $FetchBy,
            $FetchOptions,
            $ObjectOptions
        );
    }

    /**
     * Дополнительно выполняются:
     * - проверка уникальности значений полей;
     * - формирование значений 'full_path', 'created_at';
     * - установка прав доступа;
     * - обновление Sitemap.
     *
     * @see     EntityManager::add()
     * @param   AbstractEntity  $Page
     * @param   bool            $isTrusted
     * @return  CPage
     */
    public static function add(AbstractEntity $Page, bool $isTrusted = false): AbstractEntity
    {
        if (!static::isUniqueFields($Page)) {
            throw new PageEditEx( PageEditEx::IDENT_IS_NOT_UNIQUE );
        }

        static::setFullPath($Page);
        $Page->created_at = Time::toSQLDateTime();
        $Page = parent::add($Page, $isTrusted);
        static::setPermissions($Page);

        Extender::call('PageManager::onUpdate');

        return $Page;
    }

    /**
     * Дополнительно выполняются:
     * - проверка уникальности значений полей;
     * - проверка свойства 'is_fixed';
     * - обновление значений 'full_path' и 'posit';
     * - установка прав доступа;
     * - обновление Sitemap.
     *
     * @see     EntityManager::update()
     * @param   AbstractEntity  $Page
     * @param   AbstractEntity  $NewPage
     * @param   bool            $isTrusted
     * @param   bool            $withPerms  Обновить вместе с правами доступа
     * @return  CPage
     */
    public static function update(
        AbstractEntity $Page,
        AbstractEntity $NewPage,
        bool $isTrusted = false,
        bool $withPerms = true
    ): AbstractEntity {
        if (
            !static::compareUniqueFields($Page, $NewPage)
            && !static::isUniqueFields($NewPage)
        ) {
            throw new PageEditEx( PageEditEx::IDENT_IS_NOT_UNIQUE );
        }

        $NewPage->full_path = $Page->full_path;

        if ($Page->is_fixed) {
            $NewPage->setFields([
                'parent'          => $Page->parent,
                'ident'           => $Page->ident,
                'main_template'   => $Page->main_template,
                'module'          => $Page->module,
                'direct_link'     => $Page->direct_link,
                'controller_dir'  => $Page->controller_dir,
                'controller_file' => $Page->controller_file
            ]);
        } elseif ($Page->parent != $NewPage->parent) {
            static::setFullPath($NewPage);
            if (!$NewPage->canTrust('posit')) {
                static::setPosit($NewPage);
            }
        } elseif ($Page->ident !== $NewPage->ident) {
            static::setFullPath(
                $NewPage,
                substr_replace($Page->full_path, $NewPage->ident, - strLength($Page->ident) - 1, -1)
            );
        }

        $UpdateResult = parent::update($Page, $NewPage);

        if ($withPerms) {
            static::clearPermissions($Page);
            static::setPermissions($NewPage);
        }

        Extender::call('PageManager::onUpdate');

        return $UpdateResult;
    }

    /**
     * "Быстрое" обновление заголовка Страницы.
     *
     * @param  int     $pageId  ID страницы
     * @param  string  $name    Новый заголовок
     */
    public static function updateName(int $pageId, string $name): void
    {
        if (!CPageMeta::canWeDoIt(Action::UPDATE)) {
            throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
        }

        DBCommand::update(
            CPageMeta::getDBTable(),
            ['name' => $name],
            'id = ' . DBCommand::qV($pageId)
        );

        Extender::call('PageManager::onUpdate');
    }

    /**
     * Смена 'full_path' Страницы на вычисляемое или заданное значение.
     *
     * @param  CPage   $Page     Изменяемая страница
     * @param ?string  $newPath  Явно указанное новое значение 'full_path'
     */
    protected static function setFullPath(CPage $Page, ?string $newPath = null): void
    {
        $dbTable = CPageMeta::getDBTable();

        if (empty($newPath)) {
            $parentPath = DBCommand::select([
                'select' => 'full_path',
                'from'   => DBCommand::qC($dbTable),
                'where'  => 'id = ' . DBCommand::qV($Page->parent)
            ], DBCommand::OUTPUT_FIRST_CELL) ?: '/';
            $newPath = "{$parentPath}{$Page->ident}/";
        }

        if ($Page->full_path !== $newPath) {
            $oldPath = $Page->full_path;
            $Page->setTrust('full_path', $newPath);
            if ($oldPath) {
                $oldPath = DBCommand::eV($oldPath);
                $newPath = DBCommand::eV($newPath);
                $substrPos = strLength($oldPath) + 1;
                DBCommand::update(
                    $dbTable,
                    ['full_path' => "= CONCAT('{$newPath}', SUBSTRING(full_path FROM {$substrPos}))"],
                    "full_path LIKE '{$oldPath}_%'"
                );
            }
        }
    }

    /**
     * Дополнительно выполняются:
     * - проверка свойства 'is_fixed';
     * - удаление дочерних страниц;
     * - удаление прав доступа;
     * - обновление Sitemap.
     *
     * @see    EntityManager::delete()
     * @param  AbstractEntity  $Page
     * @param  bool            $isTrusted
     */
    public static function delete(AbstractEntity $Page, bool $isTrusted = true): void
    {
        if (
            $Page->is_fixed
            || !CPageMeta::canIDoThis(Action::DELETE, $isTrusted)
        ) {
            throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
        }

        if (SiteOptions::get('delete_with_subpages')) {
            DBCommand::disableConstraints();
            static::rDelete($Page);
            DBCommand::enableConstraints();
        } else {
            static::clearPermissions($Page);
            $dbTable = CPageMeta::getDBTable();
            $trashId = DBCommand::select([
                'select' => 'id',
                'from'   => DBCommand::qC($dbTable),
                'where'  => "parent = '0' AND ident = 'trash'"
            ], DBCommand::OUTPUT_FIRST_CELL);
            DBCommand::update(
                $dbTable,
                ['parent' => $trashId],
                'parent = ' . DBCommand::qV($Page->id)
            );
            parent::delete($Page, true);
        }

        Extender::call('PageManager::onUpdate');
    }

    /**
     * Рекурсивное удаление Страниц начиная с заданной.
     *
     * @param  CPage   $Page     Удаляемая страница
     * @param ?string  $dbTable  Таблица БД со страницами
     */
    protected static function rDelete(CPage $Page, ?string $dbTable = null): void
    {
        static::clearPermissions($Page);

        if ($dbTable === null) {
            $dbTable = DBCommand::qC( CPageMeta::getDBTable() );
        }
        $subPages = DBCommand::select([
            'select' => 'id',
            'from'   => $dbTable,
            'where'  => 'parent = ' . DBCommand::qV($Page->id)
        ], DBCommand::OUTPUT_ID_AS_KEY);

        foreach ($subPages as $id => $fields) {
            static::rDelete(new CPage(['id' => $id]), $dbTable);
        }

        parent::delete($Page, true);
    }

    /**
     * Обработка "перетаскивания" Страницы - изменение позиции и/или родительской Страницы.
     *
     * @param   int     $id        ID страницы, которую перетаскиваем
     * @param   int     $targetId  ID целевой страницы
     * @param   string  $point     Положение относительно целевой страницы: 'prepend'|'append'|'top'|'bottom'
     * @return  bool    Успешно ли прошла операция
     */
    public static function dnd(int $id, int $targetId, string $point): bool
    {
        $Page = static::getById($id);
        if (
            $Page->is_fixed
            || !CPageMeta::canWeDoIt(Action::UPDATE)
        ) {
            throw new AccessDeniedEx( AccessDeniedEx::YOU_CANT );
        }

        $NewPage = clone $Page;
        $NewPage->extraData['posit_target'] = $targetId;
        $NewPage->extraData['posit_point'] = $point;
        static::setPosit($NewPage);
        static::update($Page, $NewPage, false, false);

        return true;
    }

    /**
     * Получение списка дочерних Страниц, пригодного для использования с EasyUI tree.
     *
     * @param   int     $parent  ID родительской страницы
     * @param   bool    $isMap   Включить в список только страницы с 'in_map' == 1
     * @return  array
     */
    public static function getPageTree(int $parent, bool $isMap = false): array
    {
        $dbTable = CPageMeta::getDBTable();

        $where = [[
            'clause' => ":{$dbTable}:.:parent: = {parent}",
            'values' => [$parent]
        ]];

        if ($isMap) {
            $where[] = [
                'oper' => 'AND',
                'clause' => ":{$dbTable}:.:in_map: = 1 AND :state:.:in_map: = 1"
            ];
        }

        if (!User::isAdmin()) {
            $where[] = [
                'oper' => 'AND',
                'clause' => ":{$dbTable}:.:is_hidden: = 0"
            ];
        }

        return DBCommand::select([
            'select' => [
                $dbTable => ['id', 'text' => 'name', 'full_path'],
                'state' => ['state' => DBQueryBuilder::conditional(
                    DBCommand::qC('state.id') . ' IS NULL',
                    "'open'",
                    "'closed'"
                )]
            ],
            'from'   => $dbTable,
            'join'   => "LEFT JOIN {$dbTable} AS state ON {$dbTable}.id = state.parent",
            'where'  => $where,
            'group'  => "{$dbTable}.id",
            'order'  => "{$dbTable}.posit"
        ]);
    }

    /**
     * Сохранение настроек прав для различных групп пользователей
     * на основе $Page->extraData['perms'].
     *
     * @param  CPage  $Page  Страница сайта
     */
    protected static function setPermissions(CPage $Page): void
    {
        if (empty($Page->extraData['perms'])) {
            return;
        }

        $perms = [];
        foreach ($Page->extraData['perms'] as $groupId => $permCode) {
            $perms['page_id'][] = $Page->id;
            $perms['group_id'][] = $groupId;
            $perms['statuses'][] = $permCode;
        }

        DBCommand::insert(CPageMeta::getDBTablePermissions(), $perms);
    }

    /**
     * Очистка настроек прав для различных групп пользователей.
     *
     * @param  CPage  $Page  Страница сайта
     */
    protected static function clearPermissions(CPage $Page): void
    {
        DBCommand::delete(
            CPageMeta::getDBTablePermissions(),
            'page_id = ' . DBCommand::qV($Page->id)
        );
    }

    /**
     * Добавляется таблица настройки прав доступа.
     *
     * @see     EntityManager::getHtmlForSingleObjForm()
     * @param   AbstractEntity  $Page
     * @param  ?string          $action
     * @return  string
     */
    public static function getHtmlForSingleObjForm(
        AbstractEntity $Page,
        ?string $action = null
    ): string {
        $defaultHtml = parent::getHtmlForSingleObjForm($Page, $action);
        if ($action === Action::SEARCH) {
            return $defaultHtml;
        }

        $Page->pullPerms();
        Application::assign([
            'permsInfo' => CPageMeta::PERMS_DESCRIPTION,
            'allUserGroups' => UserManager::getAllGroups(),
            'curPerms' => $Page->extraData['perms']
        ]);

        return $defaultHtml . Application::getContent('entity_edit', 'cpage');
    }
}
