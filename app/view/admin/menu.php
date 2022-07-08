<?php

try {
    ClassLoader::loadClass('PageManager');

    $menu = MenuBuilder::getPages(Cfg::URL_ADMIN_PANEL, false);

    $available_modules = require __DIR__ . Cfg::DS . 'modules_list.inc.php';

    foreach ($menu as & $page) {
        if ($page['full_path'] === Application::getPageInfo('full_path')) {
            $page['active'] = true;
        }

        if ($page['ident'] === Application::PAGE_IDENT_MODULES) {
            $installed_modules = BlockManager::fetch();

            foreach ($installed_modules as $module) {
                $adminControllerFile = Cfg::DIR_MODULES . $module->ident . Cfg::DS . 'admin_controller.php';
                if (FsFile::isExists($adminControllerFile)) {
                    $page['sub_pages'][] = [
                        'name' => $module->name,
                        'full_path' => $page['full_path'] . '?module=' . $module->ident,
                        'active' => Request::getVar('module', 'string') === $module->ident
                    ];
                }

                if (isset($available_modules[$module->ident])) {
                    unset($available_modules[$module->ident]);
                }
            }
            foreach ($available_modules as $item) {
                $page['sub_pages'][] = [
                    'name' => $item['name'],
                    'full_path' => '',
                    'active' => false,
                    'description' => $item['description'] ?? ''
                ];
            }
        }
    }

    Application::assign('menu', $menu);
    Application::showContent('admin','menu');
} catch (Exception $Ex) {
    Logger::registerException($Ex);
}
