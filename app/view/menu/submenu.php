<?php

StaticResourceImporter::css("specific/submenu");

ClassLoader::loadClass('MenuBuilder');

$current_page_id = Application::getPageInfo('id');

if (Application::getPageInfo('parent') === Cfg::PAGE_ID_FRONT) {
    $first_level_page_id = (int)Application::getPageInfo('id');
} else {
    $first_level_page_id = PageManager::getFirstLevelParentPageId($current_page_id);
}

if (!$first_level_page_id) {
    return false;
}

Application::assign([
    'menu' => MenuBuilder::getItems($first_level_page_id, true),
    'current_page_id' => $current_page_id,
    'depth' => 3
]);

Application::showContent('menu', "submenu");
