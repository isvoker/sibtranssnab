<?php

try {
    require __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'init.php';

    Application::run(['breadcrumbs' => false, 'mobile' => false, 'smarty' => false]);

    $controller = Request::getVar('controller', 'string', '');
    $module = Request::getVar('module', 'string', '');
    $csrfKey = Request::getVar('csrf_key', 'string', '');

    if (!empty($module)) {
        $controller = $module;
    }

    if (
        !preg_match('/^[a-zA-Z_]+$/', $controller)
        || !Tokenizer::verifySessionToken($csrfKey)
    ) {
        Response::setStatusCode(Response::STATUS_FORBIDDEN);
        Response::close(Response::STATUS_FORBIDDEN);
    }

    ClassLoader::loadClass('Ajax');

    if (!empty($module)) {
        $controllerPath = Cfg::DIR_MODULES . $controller . Cfg::DS . 'ajax_controller.php';
    } else {
        $controllerPath = Cfg::DIR_CONTROLLERS . $controller . '.inc.php';
    }

    if (FsFile::isExists($controllerPath)) {
        if (isset($_GET['controller'])) {
            Response::setUseCache(Time::WEEK);
        } else {
            Response::setDoNotUseCache();
        }

        $action = Request::getVar('action', 'string');

        $data = Ajax::getDataBadRequest();

        require $controllerPath;
    } else {
        $data = Ajax::getDataControllerNotFound();
    }

    Ajax::submitJson($data);
} catch (RuntimeException $E) {
    exit($E->getMessage());
} catch (Throwable $E) {
    Logger::registerException($E);
    Logger::showExceptions();
} finally {
    Response::sendCookies();
}
