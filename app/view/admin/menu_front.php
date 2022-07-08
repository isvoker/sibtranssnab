<?php

ClassLoader::loadClass('PageManager');

$pages = MenuBuilder::getPages( Application::getPageInfo('id') );
$menu = [];

foreach ($pages as $page) {
	$item = [
		'id' => $page['id'],
		'name' => Html::qSC($page['name']),
        'href' => $page['full_path']
	];
	$menu[$page['id']] = $item;
}
unset($pages, $item);

Application::assign('menu_front', $menu);
Application::showContent('admin', 'menu_front');
