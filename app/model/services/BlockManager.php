<?php

ClassLoader::preloadClass('services_base/BaseBlockManager');

/**
 * Управление объектами класса [Блок страницы].
 *
 * @author Lunin Dmitriy
 */
class BlockManager extends BaseBlockManager {
    /**
     * Получение данных о модуле.
     *
     * @param   string  $ident  Идентификатор запрашиваемого блока
     * @return  array
     */
    public static function getBlockData(string $ident): array
    {
        $block = DBCommand::select([
            'from' => CBlockMeta::getDBTable(),
            'where' => DBCommand::qC('ident') . ' = ' . DBCommand::qV($ident)
        ], DBCommand::OUTPUT_FIRST_ROW);

        if (empty($block)) {
            return [];
        }

        return $block;
    }

    /**
     * Список идентификаторов модулей, который берется из названий соответствующих директорий в /modules/
     *
     * @return array
     */
    public static function getBlocksInFS(): array
    {
        $modulesDir = Cfg::DIR_MODULES;
        $results = scandir($modulesDir);
        $modules = [];

        foreach ($results as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($modulesDir . Cfg::DS . $item)) {
                $modules[] = $item;
            }
        }

        return $modules;
    }

	/**
	 * Список параметров модуля
	 *
	 * @param string $module_ident идентификатор модуля
	 * @param string|null $settings_ident идентификатор параметра
	 * @return array|string|null
	 */
	public static function getModuleSettings(string $module_ident, $settings_ident = null)
    {
        $where = 'module_ident=' . DBCommand::qV($module_ident);

        if (!empty($settings_ident)) {
            $setting = DBCommand::select([
                'select' => 'value',
                'from' => CBlockMeta::getDBTableSettings(),
                'where' => $where . ' AND ident=' . DBCommand::qV($settings_ident)
            ], DBCommand::OUTPUT_FIRST_CELL);

            if (empty($setting)) {
                return null;
            }

            return $setting;
        }

        $DBSettings = DBCommand::select([
            'from' => CBlockMeta::getDBTableSettings(),
            'where' => $where
        ]);
        $result = [];
        foreach ($DBSettings as $item) {
            $result[$item['ident']] = $item['value'];
        }
        return $result;
    }

    /**
     * Добавление нового параметра модуля
     *
     * @param   string  $moduleIdent    идентификатор модуля
     * @param   string  $settingIdent   идентификатор параметра
     * @param   mixed   $settingValue   значение параметра
     * @return  int
     */
    public static function insertModuleSetting(string $moduleIdent, string $settingIdent, $settingValue): int
    {
        return DBCommand::insert(
            CBlockMeta::getDBTableSettings(),
            [
                'module_ident' => $moduleIdent,
                'ident' => $settingIdent,
                'value' => $settingValue
            ]
        );
    }

    /**
     * Обновление параметра модуля
     *
     * @param   string  $ModuleIdent    идентификатор модуля
     * @param   string  $SettingIdent   идентификатор параметра
     * @param   mixed   $SettingValue   значение параметра
     */
    public static function updateModuleSetting(string $ModuleIdent, string $SettingIdent, $SettingValue)
    {
        DBCommand::update(
            CBlockMeta::getDBTableSettings(),
            ['value' => $SettingValue],
            'ident=' . DBCommand::qV($SettingIdent) . ' AND module_ident=' . DBCommand::qV($ModuleIdent)
        );
    }

    /**
     * Данные о связи мобудей с шаблонами.
     *
     * @param   string  $ModuleIdent    идентификатор модуля
     * @param   string  $TemplateIdent  идентификатор шаблона
     * @return  array
     */
    public static function getModulesToTemplates(): array
    {
        return DBCommand::select([
            'from' => CBlockMeta::getDBTableModulesToTemplates()
        ]);
    }

    /**
     * Привязка модуля к шаблону
     *
     * @param   string  $moduleIdent    идентификатор модуля
     * @param   string  $templateIdent  идентификатор шаблона
     */
    public static function bindWidgetToTemplate(string $moduleIdent, string $templateIdent):void
    {
        DBCommand::insert(
            CBlockMeta::getDBTableModulesToTemplates(),
            [
                'template_ident' => $templateIdent,
                'module_ident' => $moduleIdent
            ]
        );
    }

    /**
     * Привязка модуля к шаблону
     *
     * @param   string  $ModuleIdent    идентификатор модуля
     * @param   string  $TemplateIdent  идентификатор шаблона
     */
    public static function unbindWidgetFromTemplate(string $ModuleIdent, string $TemplateIdent):void
    {
        DBCommand::delete(
            CBlockMeta::getDBTableModulesToTemplates(),
            "module_ident = " . DBCommand::qV($ModuleIdent) . " AND template_ident = " . DBCommand::qV($TemplateIdent)
        );
    }

    /**
     * Содержимое видшета модуля
     *
     * @param   string  $ident  Идентификатор модуля
     * @param   array   $props  Параметры, передаваемые модулю
     */
    public static function getBlockWidget(string $ident, array $props): string
    {
        return Application::getWidget(false, false, $ident, $props);
    }

    /**
     * URL страницы, к которой подключен модуль.
     *
     * @param   string  $ident  Идентификатор модуля
     * @return  string
     */
    public static function getBlockPageUrl(string $ident): string
    {
        $block = self::getBlockData($ident);

        if (empty($block)) {
            return '';
        }

        return (string)DBCommand::select([
            'select' => 'full_path',
            'from' => CPageMeta::getDBTable(),
            'where' => 'module=' . DBCommand::qV($block['ident']),
        ], DBCommand::OUTPUT_FIRST_CELL);
    }

    /**
     * @return string
     */
    public static function getAdminBlocksPageUrl():string
    {
        return Cfg::URL_ADMIN_PANEL . Application::PAGE_IDENT_MODULES . '/';
    }

    public static function getAdminBlockPath(string $module): string
    {
        return self::getAdminBlocksPageUrl() . "?module={$module}";
    }

    public static function getIcon($ident): string
    {
        $path = "/modules/{$ident}/tpl/images/main-icon.png";

        if (FsFile::isExists(FsDirectory::normalizePath(Cfg::DIRS_ROOT . $path))) {
            return $path;
        }
        return '';
    }
}
