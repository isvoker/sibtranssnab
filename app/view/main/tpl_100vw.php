<?php

require 'events.inc.php';
require 'events_specific.inc.php';

Application::loadPageBlock();
Application::showContent('main', 'tpl_100vw' . TPL_NAME_SUFFIX);
