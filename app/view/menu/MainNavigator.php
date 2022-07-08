<?php

ClassLoader::loadClass('MenuBuilder');

Application::assign([
    'depth' => Application::isMobileSite() ? 2 : 4,
    'show_front' => true,
    'menu' => MenuBuilder::getItems(Cfg::PAGE_ID_FRONT, true)
]);

Application::showContent('menu','MainNavigator' . TPL_NAME_SUFFIX);
