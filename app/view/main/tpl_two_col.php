<?php

require 'events.inc.php';
require 'events_specific.inc.php';

Application::onBeforePageBlockLoaded(static function() {
    Application::assign([
        'submenu' => Application::getWidget('menu', 'submenu')
    ]);
});

Application::loadPageBlock();
Application::showContent('main', 'tpl_two_col' . TPL_NAME_SUFFIX);
