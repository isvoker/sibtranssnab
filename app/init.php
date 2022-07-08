<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Cfg.php';

if (Cfg::DEBUG_IS_ON) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
}

require Cfg::DIR_FUNCTIONS . 'functions.php';
require Cfg::DIR_MODEL . 'core' . DIRECTORY_SEPARATOR . 'ClassLoader.php';
require Cfg::DIR_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

ClassLoader::init();

Logger\LogToFile::addHandler();
if (Cfg::DEBUG_IS_ON) {
    Logger\LogToFirePHP::addHandler()
    || Logger\LogToChromePHP::addHandler()
    || Logger\LogToHtml::addHandler();
}
Logger::init();

Response::setContentType();
Cookies::loadRequestCookies();
Session::open();
DBCommand::connect();
Application::checkDbIsEmpty();
Guard::initialCheck();
Tokenizer::makeSessionToken();

ClassLoader::addComponent('specific');
ClassLoader::addModulesComponents();

StaticResourceImporter::addModulesComponents();

include __DIR__ . DIRECTORY_SEPARATOR . 'hooks.php';
