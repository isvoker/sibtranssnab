<?php

$breadcrumbs = Application::getBreadcrumbs();

$breadcrumbs[count($breadcrumbs) - 1]['url'] = '#';

Application::assign('breadcrumbs', $breadcrumbs);
Application::showContent('special', 'breadcrumbs');
