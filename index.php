<?php

try {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'init.php';

    Application::run();
    User::doAction();
} catch (Throwable $E) {
    if (class_exists('Logger', false)) {
        Logger::registerException($E);
    } else {
        exit($E->getMessage());
    }
}

try {
    Application::detectPage();
    Application::showPage();
} catch (Throwable $E) {
    Logger::registerException($E);
    Logger::showExceptions();
}
