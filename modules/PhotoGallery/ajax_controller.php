<?php

try {
    $module = PhotoGallery::class;

    DBCommand::begin();

    switch ($action) {
        case 'get_images':
            $options = Request::getVar('data', 'array');

            if (!$options) {
                break;
            }

            $part = (!empty($options['part'])) ? (int)$options['part'] + 1 : false;
            $is_widget = (!empty($options['is_widget'])) ? (bool)$options['is_widget'] : false;

            $limit = ImageInAlbumManager::getLimit($is_widget);

            Application::assign([
                'galleryImages' => objectToArray(
                	ImageInAlbumManager::getImages($options['album_id'], $is_widget, $part)
                )
            ]);

            $data = Ajax::getDataOk();
            $data['html'] = Application::getContent('modules', "{$module}/tpl/images.inc");

            if ((($part - 1) * $limit + $limit) < ImageInAlbumManager::count()) {
                $data['part'] = $part;
            }

            break;

	    case 'update_settings':
		    $settings = Request::getVar('settings', 'array');

		    if (!$settings) {
			    break;
		    }

		    foreach ($settings as $ident => $value) {
			    BlockManager::updateModuleSetting($module, $ident, $value);
		    }

		    $data = Ajax::getDataOk();

		    break;

        case 'AdminAlbumRemove':
            if (!User::isAdmin()) {
                throw new AccessDeniedEx();
            }

            $albumId = (int)Request::getVar('album_id', 'numeric');

            if (!$albumId) {
                break;
            }

            AlbumManager::delete(
            	AlbumManager::getById($albumId)
            );

            $data = Ajax::getDataOk();

            break;

        case 'albums_set_position':
            if (!User::isAdmin()) {
                throw new AccessDeniedEx();
            }

            $fields = Request::getVar('data', 'array');

            if (!$fields) {
                break;
            }

            foreach ($fields as $Row) {
                AlbumManager::setAlbumPosit($Row['id'], $Row['posit']);
            }

            $data = Ajax::getDataOk();

            break;

        case 'admin_add_images_to_album':
            if (!User::isAdmin()) {
                throw new AccessDeniedEx();
            }

            $images_data = Request::getVar('data', 'array');

            if (!$images_data
                || empty($images_data['album_id'])
                || empty($images_data['images_urls'])
                || !is_array($images_data['images_urls'])
            ) {
                break;
            }

	        $albumId = (int)$images_data['album_id'];

            $FileMimeTypePattern = '/^image\/(gif|p?jpe?g|png)$/i';

            $data = Ajax::getDataOk();

            foreach ($images_data['images_urls'] as $ImageUrl) {
                $ImageUrl = urldecode($ImageUrl);

                $FilePath = Cfg::DIRS_ROOT . $ImageUrl;
                $FileMimeType = mime_content_type($FilePath);

                if (!file_exists($FilePath)) {
                    $data = Ajax::getDataBadRequest();
                    throw new RuntimeException('Указан несуществующий файл');
                }

                if (!preg_match($FileMimeTypePattern, $FileMimeType)) {
                    $data = Ajax::getDataBadRequest();
                    throw new RuntimeException('Недопустимый тип файла. Необходимо выбрать JPG, PNG или GIF изображение');
                }

	            $ModulePagePath = BlockManager::getAdminBlockPath($module);

	            $Image = ImageInAlbumManager::add(
                	new CImageInAlbum([
                		'album_id' => $albumId,
		                'img_path' => $ImageUrl
	                ])
                );

	            $Image->buildExtraData();

                Application::assign([
                    'image' => objectToArray($Image)
                ]);

                $data['selected_images'][] = Application::getContent('modules',"{$module}/tpl/admin_image.inc");
            }

            break;

        case 'admin_images_remove':
            if (!User::isAdmin()) {
                throw new AccessDeniedEx();
            }

            $imagesIds = Request::getVar('data', 'array');

            if (!$imagesIds) {
                break;
            }

            foreach ($imagesIds as $id) {
	            ImageInAlbumManager::delete(
		            ImageInAlbumManager::getById($id)
	            );
            }

            $data = Ajax::getDataOk();

            break;

        case 'admin_images_move_to_album':
            if (!User::isAdmin()) {
                throw new AccessDeniedEx();
            }

            $fields = Request::getVar('data', 'array');

            if (!$fields) {
                break;
            }

            PhotoGallery::moveGalleryImages($fields['images_ids'], $fields['album_id']);

            $data = Ajax::getDataOk();

            break;

        default:
            break;
    }

    DBCommand::commit();
} catch (Exception $E) {
    DBCommand::rollback();
    $data = Ajax::getDataError($E);
}
