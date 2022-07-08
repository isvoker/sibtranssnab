<?php

if (!defined('MODULE_INSTALLATION')) {
    die('Incorrect call point.');
}

$module = PhotoGallery::class;

$tables = [
    'albums' => CAlbumMeta::getDBTable(),
    'images' => CImageInAlbumMeta::getDBTable(),
	'pages'  => CPageMeta::getDBTable()
];

# Module registration
BlockManager::add(
    new CBlock([
        'ident' => $module,
        'name' => 'Фотогалерея',
        'is_page' => 1,
        'is_widget' => 1
    ])
);

# Creation special sets
$settings = [
    'photos_per_page' => 24,
    'photos_per_page_in_widget' => 12,
    'widget_is_enabled' => 1
];
foreach ($settings as $ident => $value) {
    BlockManager::insertModuleSetting($module, $ident, $value);
}

# Triggers registration
Application::insertTrigger($module, 'PhotoGallery', 'runWhenSitemapLoads');
Application::insertTrigger($module, 'PhotoGallery', 'applyGalleryOverrides');

# Module special tables
DBCommand::query(
    "CREATE TABLE IF NOT EXISTS `{$tables['albums']}` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `page_id` int(10) UNSIGNED DEFAULT NULL,
        `parent` int(10) UNSIGNED DEFAULT NULL,
        `posit` smallint(2) UNSIGNED DEFAULT '1',
        `name` varchar(255) NOT NULL,
        `cover` varchar(255) DEFAULT NULL,
        `content` mediumtext DEFAULT NULL,
        `is_hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `IX_page_id` (`page_id`),
        KEY `IX_parent` (`parent`),
        KEY `IX_posit` (`posit`),
        CONSTRAINT `FK_albums__pages` FOREIGN KEY (`page_id`)
          REFERENCES `{$tables['pages']}` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Альбомы галереи'"
);

DBCommand::query(
    "CREATE TABLE IF NOT EXISTS `{$tables['images']}` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `album_id` int(10) UNSIGNED NOT NULL,
        `img_path` varchar(255) NOT NULL,
        `name` varchar(255) DEFAULT NULL,
        `caption` varchar(255) DEFAULT NULL,
        `posit` smallint(2) UNSIGNED DEFAULT '1',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `IX_album_id` (`album_id`),
        KEY `IX_posit` (`posit`),
        CONSTRAINT `FK_images__albums` FOREIGN KEY (`album_id`) 
          REFERENCES `{$tables['albums']}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Изображения галереи'"
);
