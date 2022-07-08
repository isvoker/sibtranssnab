<?php

ClassLoader::loadClass('MiniBannerManager');

$page = Request::getVar('page', 'numeric', 1);

$MiniBanners = MiniBannerManager::fetch(
    null,
    (new FetchOptions())
        ->setCount()
        ->setLimit()
        ->setPage($page),
    (new ObjectOptions())
        ->setWithExtraData()
        ->setForOutput()
);

foreach ($MiniBanners as $banner) {
    $banner->extraData['image_resized'] = FsImage::safeResize($banner->image, ['dstW' => 140]);
}

Application::assign([
	'mini_banners' => objectToArray($MiniBanners),
	'insBtn' => CMiniBanner::getInsertButton(),
	'paginator' => Html::paginator($page, MiniBannerManager::count()),
]);
Application::showContent('admin', 'mini_banners');
