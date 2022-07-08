<?php

Application::assign([
	'menu_front' => Application::getWidget('admin', 'menu_front')
]);
Application::showContent('admin', 'front');
