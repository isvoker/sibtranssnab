<?php

require 'events.inc.php';

Application::onBeforePageBlockLoaded(static function() {
    StaticResourceImporter::js('ckeditor');
    StaticResourceImporter::js('ckfinder');

    StaticResourceImporter::css('admin');
    StaticResourceImporter::js('admin');

    Application::assign([
        'AdminPanelPath' => Cfg::URL_ADMIN_PANEL,
        'system_messages' => Application::getWidget('admin', 'system_notifications'),
        'menu' => Application::getWidget('admin', 'menu'),
    ]);
});

Application::onPageBlockLoaded(static function() {
    Application::assign([
        'breadcrumbs' => Application::getWidget('admin', 'breadcrumbs'),
    ]);
});

Application::loadPageBlock();
Application::showContent('main', 'tpl_admin');
