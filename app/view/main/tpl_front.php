<?php

require 'events.inc.php';
require 'events_specific.inc.php';

Application::loadPageBlock();
Application::showContent('main', 'tpl_front' . TPL_NAME_SUFFIX);
