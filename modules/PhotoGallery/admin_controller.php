<?php

$module = PhotoGallery::MODULE_IDENT;
$adminModulePath = BlockManager::getAdminBlockPath($module);

StaticResourceImporter::js("{$module}/admin");
StaticResourceImporter::css("{$module}/admin");

$section = Request::getVar('section', 'string', '');

switch ($section) {
	case 'settings':
		include_once __DIR__ . Cfg::DS . 'admin_controller_settings.inc.php';
		break;

	case 'albums':
		require_once __DIR__ . Cfg::DS . 'admin_controller_albums.inc.php';
		break;

	case 'images':
        require_once __DIR__ . Cfg::DS . 'admin_controller_images.inc.php';
        break;

	default:
		Application::assign([
			'module_menu' => [
				[
					'url' => "{$adminModulePath}&section=settings",
					'name' => 'Настройки'
				],
				[
					'url' => "{$adminModulePath}&section=albums",
					'name' => 'Альбомы',
					'description' => 'Добавление, редактирование, сортировка и удаление альбомов'
				],
				[
					'url' => "{$adminModulePath}&section=images",
					'name' => 'Изображения',
					'description' => 'Добавление, редактирование и удаление изображений'
				]
			]
		]);
		Application::showContent('modules', "{$module}/tpl/admin_front");

		break;
}
