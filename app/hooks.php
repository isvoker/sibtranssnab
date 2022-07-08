<?php
/** Расширение встроенной функциональности */

// События, выполняющиеся после обновления Страницы сайта
Extender::add('PageManager::onUpdate', static function() {
	Sitemap::make();
});

// Получение дополнительных пунктов для карты сайта
Extender::add('Sitemap::getItems', static function() {
    $items = [];
    if ((int)SiteOptions::get('insert_specific_pages_in_sitemap')) {
        $modules_output = Application::runTrigger('runWhenSitemapLoads');
        foreach ($modules_output as $module_urls) {
            $items = array_merge($items, $module_urls);
        }
    }

    return $items;
});
