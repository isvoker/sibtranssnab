<?php

$breadcrumbs = Application::getBreadcrumbs();
$count = count($breadcrumbs);

if ($count < 3) {
    return false;
}

$breadcrumbs[$count - 1]['url'] = '#';
unset($breadcrumbs[0]);

Application::assign('breadcrumbs', $breadcrumbs);
Application::showContent('admin', 'breadcrumbs');
