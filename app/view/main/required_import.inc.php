<?php
/**
 * Импорт обязательных для всех шаблонов JS- и CSS-файлов
 */

StaticResourceImporter::addComponent('application');

StaticResourceImporter::css('normalize');

StaticResourceImporter::js('ext/polyfills');

StaticResourceImporter::js('jquery');

StaticResourceImporter::css('ext/jquery.jgrowl');
StaticResourceImporter::js('ext/jquery.jgrowl');
StaticResourceImporter::css('ext/jquery.uitotop');
StaticResourceImporter::js('ext/jquery.uitotop');
StaticResourceImporter::js('ext/jquery.easing');
StaticResourceImporter::js('ext/jquery.form');
StaticResourceImporter::js('ext/jquery.blockUI');

if (Application::getPageProperty('fancybox')) {
    StaticResourceImporter::css('ext/jquery.fancybox');
    StaticResourceImporter::js('ext/jquery.fancybox');
}

if (Application::getPageProperty('easyui')) {
    ClassLoader::loadClass('EasyUILoader');
    EasyUILoader::putAllPlugins();

    StaticResourceImporter::js('sensei-core');
    StaticResourceImporter::js('sensei-ui');
    StaticResourceImporter::js('sensei-forms');
    StaticResourceImporter::js('ext/sensei-easyui');
} else {
    StaticResourceImporter::js('sensei-core');
    StaticResourceImporter::js('sensei-ui');
    StaticResourceImporter::js('sensei-forms');
}

if (User::isAdmin()) {
    StaticResourceImporter::js('entity-edit_btns');
}

if (Application::getPageProperty('dics')) {
    StaticResourceImporter::js('dictionaries-init');
}

StaticResourceImporter::css('flexbox');
StaticResourceImporter::css('common');
StaticResourceImporter::css('buttons');
StaticResourceImporter::css('form');
