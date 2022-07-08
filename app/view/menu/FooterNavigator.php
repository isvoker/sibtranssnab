<?php

ClassLoader::loadClass('MenuBuilder');

if (Application::isMobileSite()) {
    return false;
}

Application::assign([
    'show_front' => true,
    'menu' => MenuBuilder::getItems()
]);
Application::showContent('menu', 'FooterNavigator');
