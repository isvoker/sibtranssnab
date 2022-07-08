<?php

$Action = 'LIST';

if ($ident = Request::getVar('module', 'string', '')) {
    $Action = 'MODULE';
}

switch ($Action) {
    case 'MODULE':
        $module = BlockManager::getBlockData($ident);
        if (empty($module)) {
            echo 'Модуль "' . $ident . '" не найден в системе';
            return false;
        }
        $controller = Cfg::DIR_MODULES . $ident . Cfg::DS . 'admin_controller.php';
        if (FsFile::isExists($controller)) {
            RTP::setPageName("Модуль «{$module['name']}»");
            Application::addBreadcrumbs("Модуль «{$module['name']}»", BlockManager::getAdminBlockPath($ident));
            define('MODULE_ADMINISTRATION', true);
            require_once $controller;
        } else {
            echo 'Отсутствует файл контроллера модуля или нет права на чтение';
            return false;
        }
        break;

    case 'LIST':
    default:
        $Blocks = BlockManager::fetch(
            null,
            null,
            (new ObjectOptions())->setWithExtraData()
        );
        $main_templates = Application::getMainTemplates();
        $modules_to_templates = BlockManager::getModulesToTemplates();

        foreach ($Blocks as $module) {
            $AdminControllerFile = Cfg::DIR_MODULES . $module->ident . Cfg::DS . 'admin_controller.php';
            $ModuleInfoFile = Cfg::DIR_MODULES . $module->ident . Cfg::DS . 'info.php';

            if (FsFile::isExists($AdminControllerFile)) {
                $module->extraData['link'] = Application::getPageInfo('full_path') . '?module=' . $module->ident;
            }

            if (FsFile::isExists($ModuleInfoFile)) {
                $module->extraData['info'] = require $ModuleInfoFile;
            }

            if ($module->is_widget) {
                $module_templates = [];

                foreach ($modules_to_templates as $mt) {
                    if ($mt['module_ident'] === $module->ident) {
                        $module_templates[] = $mt['template_ident'];
                    }
                }

                foreach ($main_templates as $template) {
                    $module->extraData['templates'][] = [
                        'ident' => $template['ident'],
                        'name' => $template['name'],
                        'active' => in_array($template['ident'], $module_templates, true)
                    ];
                }
            }
        }

        Application::assign('modules', objectToArray($Blocks));
        Application::showContent('admin', 'modules');
        break;
}
