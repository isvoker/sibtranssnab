<?php

$image_path = null;
$image_height = 300;

if (Application::isDesktopSite()) {
    $image_path = SiteOptions::get('main_banner');

    if (!empty($image_path)) {
        $image_full_path = Cfg::DIRS_ROOT . urldecode($image_path);
        if (file_exists($image_full_path)) {
            $image_sizes = getimagesize($image_full_path);
            if (!empty($image_sizes[1])) {
                $image_height = (int)$image_sizes[1];
            }
        }
    }

    if ((int)SiteOptions::get('form_on_banner')) {
        Application::assign('form_on_banner', Application::getContent('special', 'callback_form_on_banner'));
    }
}

Application::assign([
    'text_content' => Application::getPageTextContent(),
    'mini_banners' => Application::getWidget('specific', 'mini_banners'),
    'main_banner' => $image_path,
    'main_banner_height' => $image_height
]);

Application::showContent('special', 'front' . TPL_NAME_SUFFIX);
