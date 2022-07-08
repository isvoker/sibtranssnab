<?php

require 'events.inc.php';
require 'events_specific.inc.php';

Application::loadPageBlock();
Application::showContent('main', 'tpl_text' . TPL_NAME_SUFFIX);
