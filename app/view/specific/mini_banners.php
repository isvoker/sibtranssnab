<?php

ClassLoader::loadClass('MiniBannerManager');

$banners = MiniBannerManager::fetch(
	null,
	(new FetchOptions())->setLimit(8),
	(new ObjectOptions())->setWithExtraData()->setForOutput()
);

if (empty($banners)) {
    return false;
}

foreach ($banners as $banner) {
    $banner->extraData['image_resized'] = FsImage::safeResize($banner->image, ['dstW'=> 250]);

    if (preg_match('#\.(jpg|jpeg|png)$#i', $banner->url)) {
        $banner->extraData['link_is_image'] = true;
    } else {
        $banner->extraData['link_is_image'] = false;
    }
}

StaticResourceImporter::css('ext/jquery.fancybox');
StaticResourceImporter::js('ext/jquery.fancybox');

Application::assign([
    'mini_banners' => objectToArray($banners),
    'insBtn' => CMiniBanner::getInsertButton(),
]);
Application::showContent('specific', 'mini_banners');
