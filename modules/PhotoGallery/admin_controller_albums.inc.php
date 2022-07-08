<?php

$module = PhotoGallery::MODULE_IDENT;
$ModuleFrontPagePath = BlockManager::getAdminBlockPath($module);
$ModuleSectionPagePath = "{$ModuleFrontPagePath}&section=albums";

StaticResourceImporter::js('entity-edit');
StaticResourceImporter::js('clipboard');

Application::addBreadcrumbs('Альбомы', $ModuleSectionPagePath);

$parentAlbum = [];

$parentAlbumId = (int)Request::getVar('album_id', 'numeric');

if (!empty($parentAlbumId)) {
	$parentAlbum = AlbumManager::getById(
		$parentAlbumId,
		(new ObjectOptions())->setWithExtraData()
	);
	$parentAlbum->extraData['images_url'] = "{$ModuleFrontPagePath}&section=images&album_id={$parentAlbum->id}";

	AlbumManager::addAlbumsToAdminBreadcrumbs($parentAlbum->id);
}

$mode = Request::getVar('mode', 'string');

switch ($mode) {
    case 'insert':
	    Application::addBreadcrumbs('Добавление альбома');
	    RTP::setPageName('Добавление альбома');

	    echo EntityRender::singleObjForm(
		    new CAlbum(['parent' => $parentAlbumId]),
		    Action::INSERT,
		    [
			    'formType' => ['vertical', 'need-validation'],
			    'buttons' => [['href' => Application::getBackLink(), 'label' => '<- Назад']]
		    ]
	    );

        break;

    case 'update':
	    Application::addBreadcrumbs('Редактирование альбома');
	    RTP::setPageName('Редактирование альбома');

        $id = (int)Request::getVar('album_id', 'string');

	    echo EntityRender::singleObjForm(
		    AlbumManager::getById($id),
		    Action::UPDATE,
		    [
			    'formType' => ['vertical', 'need-validation'],
			    'buttons' => [['href' => Application::getBackLink(), 'label' => '<- Назад']]
		    ]
	    );

        break;

    default:
        StaticResourceImporter::js('ext/jquery.ui');

		$GalleryAlbums = AlbumManager::fetch(
			(new FetchBy())->and([
				'parent' => !empty($parentAlbumId) ? $parentAlbumId : 'NULL'
			]),
			(new FetchOptions())
				->setOrderBy(['posit' => DBQueryBuilder::ASC]),
			(new ObjectOptions())
				->setForOutput()
		);

		foreach ($GalleryAlbums as $album) {
			$album->extraData['widget_code'] = PhotoGallery::getWidgetCode($album->id);
			$album->extraData['update_url'] = "{$ModuleSectionPagePath}&mode=update&album_id={$album->id}";
			$album->extraData['albums_url'] = "{$ModuleSectionPagePath}&album_id={$album->id}";
			$album->extraData['images_url'] = "{$ModuleFrontPagePath}&section=images&album_id={$album->id}";

			$album->extraData['images_count'] = ImageInAlbumManager::getCountInAlbum($album->id);

			$preview_images = ImageInAlbumManager::fetch(
				(new FetchBy())->and(['album_id' =>$album->id]),
				(new FetchOptions())->setLimit(5)
			);
			foreach ($preview_images as $image) {
				$image->extraData['img_th_path'] = FsImage::safeResize($image->img_path, ['dstW' => 150]);
			}
			$album->extraData['preview_images'] = $preview_images;
		}

        $GalleryAlbumInsertPath = "{$ModuleSectionPagePath}&mode=insert";
        if (!empty($parentAlbumId)) {
            $GalleryAlbumInsertPath .= "&album_id={$parentAlbumId}";
        }

		if ($parentAlbum) {
			RTP::setPageName("Альбом &laquo;{$parentAlbum->name}&raquo;");
		} else {
			RTP::setPageName('Альбомы');
		}

        Application::assign([
            'parentAlbum' => objectToArray($parentAlbum),
            'albums' => objectToArray($GalleryAlbums),
            'insertAlbumUrl' => $GalleryAlbumInsertPath,
            'backlink' => Application::getBackLink()
        ]);

		Application::showContent('modules', "{$module}/tpl/admin_albums_list");

        break;
}
