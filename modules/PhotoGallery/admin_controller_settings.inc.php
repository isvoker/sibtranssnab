<?php

$module = PhotoGallery::class;

StaticResourceImporter::js("{$module}/admin");

Application::addBreadcrumbs('Настройки');
RTP::setPageName('Настройки');

Application::assign([
    'settings' => BlockManager::getModuleSettings($module),
    'backlink' => Application::getBackLink()
]);
Application::showContent('modules', "{$module}/tpl/admin_settings");
