<?php

$module = PhotoGallery::MODULE_IDENT;

if (Application::isMobileVersion()) {
	StaticResourceImporter::css("{$module}/style.m");
} else {
	StaticResourceImporter::css("{$module}/style");
}

if (User::isAdmin()) {
	Application::assign(
		'galleryAdminUrl',
		BlockManager::getAdminBlockPath(PhotoGallery::class)
	);
}

$CurrentAlbumId = false;
$CurrentAlbum = false;
$loadButton = false;
$PhotoImages = [];

if (
	($CurrentAlbumId = Request::getVar('album', 'numeric'))
	|| ($CurrentAlbumId = $this->album)
) {
	StaticResourceImporter::css('ext/jquery.fancybox');
	StaticResourceImporter::js('ext/jquery.fancybox');

	StaticResourceImporter::js("{$module}/action");

	$CurrentAlbum = AlbumManager::getById(
		$CurrentAlbumId,
		(new ObjectOptions())->setForOutput()
	);

	$Albums = AlbumManager::fetch(
		(new FetchBy())
			->and(['parent' => $CurrentAlbumId]),
		(new FetchOptions())
			->setOrderBy(['created_at' => DBQueryBuilder::DESC]),
		(new ObjectOptions())
			->setForOutput()
			->setWithExtraData()
	);

	$PhotoImages = ImageInAlbumManager::getImages($CurrentAlbumId);

	if ((count($PhotoImages) < ImageInAlbumManager::count())) {
		$loadButton = true;
	}

	RTP::setPageName($CurrentAlbum->name);
	RTP::setPageTitle($CurrentAlbum->name);

	if (!empty($CurrentAlbum->seo_keywords)) {
		RTP::setKeywords($CurrentAlbum->seo_keywords);
	}

	if (!empty($CurrentAlbum->seo_description)) {
		RTP::setDescription($CurrentAlbum->seo_description);
	}

} else {

	$pageId = Application::getPageInfo('id');

	$Albums = AlbumManager::fetch(
		(new FetchBy())
			->and(['page_id' => $pageId, 'parent' => 'NULL']),
		(new FetchOptions())
			->setOrderBy(['posit' => DBQueryBuilder::DESC]),
		(new ObjectOptions())
			->setForOutput()
			->setWithExtraData()
	);

	if (empty($Albums)) {
		Application::showContent('modules', "{$module}/tpl/empty_gallery");
		return false;
	}
}

foreach ($Albums as $album) {
	if ($album->cover) {
		$album->extraData['cover_resized'] = FsImage::safeResize($album->cover, ['dstW' => 250]);
	} else {
		$album->extraData['cover_resized'] = Cfg::DEFAULT_IMAGE_WEB;
	}
}

# Crumbs
if (Application::isMobileVersion()) {
    if (!empty($CurrentAlbum)) {
        if (!empty($CurrentAlbum->parent)) {
            Application::assign([
                'BackLink' => [
                    'name' => 'Назад',
                    'url' => AlbumManager::makeAlbumUrl($CurrentAlbum->parent),
                ]
            ]);
        } else {
            Application::assign([
                'BackLink' => [
                    'name' => 'Назад',
                    'url' => Application::getPageInfo('full_path')
                ]
            ]);
        }
    }
} else {
    if (!empty($CurrentAlbumId)) {
        AlbumManager::addAlbumsToBreadcrumbs($CurrentAlbum->id);
    }
}

Application::assign([
	'textContent' => Application::getPageTextContent(),
    'galleryCurrentAlbum' => objectToArray($CurrentAlbum),
    'galleryAlbums' => objectToArray($Albums),
    'galleryImages' => objectToArray($PhotoImages),
    'galleryShowLoadButton' => $loadButton
]);

Application::showContent('modules', "{$module}/tpl/gallery" . TPL_NAME_SUFFIX);
