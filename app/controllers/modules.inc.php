<?php

try {
    if (!User::isAdmin()) {
        throw new AccessDeniedEx();
    }

    switch ($action) {
        case 'module_install':
            DBCommand::begin();

            $module = trim(Request::getVar('ident', 'string', false));

            if (empty($module)) {
                throw new RuntimeException('Передан некорректный идентификатор модуля');
            }

            $DBCheck = DBCommand::select([
                'select' => 'id',
                'from' => CBlockMeta::getDBTable(),
                'where' => 'ident=' . DBCommand::qV($module),
            ], DBCommand::OUTPUT_FIRST_CELL);

            if (!empty($DBCheck)) {
                throw new RuntimeException('Модуль уже установлен');
            }

            $InstallFile = Cfg::DIR_MODULES . $module . Cfg::DS . 'install.php';
            $ControllerFile = Cfg::DIR_MODULES . $module . Cfg::DS . 'controller.php';
            $AdminControllerFile = Cfg::DIR_MODULES . $module . Cfg::DS . 'admin_controller.php';

            if (!FsFile::isExists($ControllerFile)) {
                throw new RuntimeException('Отсутствует файл контроллера модуля или нет права чтение');
            }

            if (FsFile::isExists($InstallFile)) {
                define('MODULE_INSTALLATION', true);
                require $InstallFile;
            } else {
                throw new RuntimeException('Отсутствует файл установки модуля или нет права на чтение');
            }

            $data = Ajax::getDataOk();

            $data['message'] = 'Модуль "' . $module . '" успешно установлен.';
            if (FsFile::isExists($AdminControllerFile)) {
                $data['message'] .= ' <a class="link" href="' . BlockManager::getAdminBlockPath($module) . '">Перейти к управлению</a>';
            }

            DBCommand::commit();

            break;

        case 'bind_module_to_template':
            $is_checked = Request::getVar('is_checked', 'numeric');
            $template_ident = Request::getVar('template_ident', 'string', '');
            $module_ident = Request::getVar('module_ident', 'string', '');

            if ($is_checked === null) {
                break;
            }

            DBCommand::begin();

            if ($is_checked) {
                BlockManager::bindWidgetToTemplate($module_ident, $template_ident);
            } else {
                BlockManager::unbindWidgetFromTemplate($module_ident, $template_ident);
            }

            $data = Ajax::getDataOk();

            DBCommand::commit();

            break;
    }

} catch (Throwable $E) {
    DBCommand::rollback();
    $data = Ajax::getDataError($E);
}
