<?php

$module = PhotoGallery::MODULE_IDENT;
$ModuleFrontPagePath = BlockManager::getAdminBlockPath($module);
$ModuleSectionPagePath = "{$ModuleFrontPagePath}&section=images";

Application::addBreadcrumbs('Альбомы', "{$ModuleFrontPagePath}&section=albums");

$GalleryImages = [];
$currentAlbum = [];

$albumId = (int)Request::getVar('album_id', 'numeric');

if (!empty($albumId)) {
	$currentAlbum = AlbumManager::getById(
		$albumId,
		(new ObjectOptions())->setWithExtraData()
	);
	AlbumManager::addAlbumsToAdminBreadcrumbs($currentAlbum->id);
}

Application::addBreadcrumbs('Изображения', "{$ModuleSectionPagePath}&album_id=$albumId");

$mode = Request::getVar('mode', 'string');

switch ($mode) {
    case 'update':
	    Application::addBreadcrumbs('Редактирование изображения');
	    RTP::setPageName('Редактирование изображения');

	    StaticResourceImporter::js('entity-edit');

	    $id = (int)Request::getVar('id', 'string');

	    echo EntityRender::singleObjForm(
		    ImageInAlbumManager::getById($id),
		    Action::UPDATE,
		    [
			    'formType' => ['vertical', 'need-validation'],
			    'buttons' => [['href' => Application::getBackLink(), 'label' => '<- Назад']]
		    ]
	    );

        break;

    default:
        StaticResourceImporter::css('ext/jquery.fancybox');
        StaticResourceImporter::js('ext/jquery.fancybox');

        if ($albumId) {
	        $GalleryImages = ImageInAlbumManager::fetch(
		        (new FetchBy())->and(['album_id' => $albumId]),
		        (new FetchOptions())
			        ->setOrderBy(['posit' => DBQueryBuilder::ASC]),
		        (new ObjectOptions())
			        ->setForOutput()
			        ->setWithExtraData()
	        );
        }

	    $albumsList = AlbumManager::fetch();

        foreach ($albumsList as $album) {
	        $album->extraData['admin_url'] = "{$ModuleSectionPagePath}&album_id={$album->id}";
        }

        if ($currentAlbum) {
	        RTP::setPageName("Изображения альбома &laquo;{$currentAlbum->name}&raquo;");
        } else {
	        RTP::setPageName('Изображения');
        }

        Application::assign([
	        'albums' => objectToArray($albumsList),
        	'currentAlbum' => objectToArray($currentAlbum),
            'images' => objectToArray($GalleryImages),
            'backlink' => Application::getBackLink(),
	        'imagePagePath' => $ModuleSectionPagePath
        ]);

	    Application::showContent('modules', "{$module}/tpl/admin_images_list");

        break;
}
