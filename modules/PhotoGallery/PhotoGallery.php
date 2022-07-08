<?php

class PhotoGallery
{
    public const MODULE_IDENT = 'PhotoGallery';

    public static function getWidgetCode(int $id): string
    {
        return "[photogallery={$id}]";
    }

    public static function moveGalleryImages(array $imagesIds, int $albumId): void
    {
        if (!$imagesIds || !$albumId) {
            return;
        }

        $ids_str = implode(',', $imagesIds);

        DBCommand::update(
            CImageInAlbumMeta::getDBTable(),
            ['album_id' => $albumId],
            "id IN ({$ids_str})"
        );
    }

    #TRIGGERS

    public static function runWhenSitemapLoads(array $Data = []): array
    {
        $URLSET = [];

	    $Albums = AlbumManager::fetch(
		    null,
		    null,
		    (new ObjectOptions())
		        ->setWithExtraData()
	    );

	    foreach ($Albums as $album) {
		    $URLSET[] = [
			    'loc' => AlbumManager::makeAlbumUrl($album->id),
			    'lastmod' => $album->update_at
		    ];
	    }

        return $URLSET;
    }

	/**
	 * Поиск и замена в тексте шаблонов вида [photogallery=CODE] на соответствующие виджеты фотогалереи
	 *
	 * @param   string  $content
	 * @return  string
	 */
	public static function applyGalleryOverrides(string $content): string
	{
		$module = self::class;
		$settings = BlockManager::getModuleSettings($module);

		if (empty($settings['widget_is_enabled']) || !$content) {
			return '';
		}

		preg_match_all(
			"#\[photogallery=([\d]+)]#",
			$content,
			$matches
		);

		if (empty($matches[1])) {
			return '';
		}

		StaticResourceImporter::css('ext/jquery.fancybox');
		StaticResourceImporter::js('ext/jquery.fancybox');

		$albumIds = $matches[1];

		StaticResourceImporter::js("{$module}/action");

		if (Application::isMobileSite()) {
			StaticResourceImporter::css("{$module}/style.m");
		} else {
			StaticResourceImporter::css("{$module}/style");
		}

		foreach ($albumIds as $id) {
			$AdminLink = false;
			if (User::isAdmin()) {
				$AdminLink = BlockManager::getAdminBlockPath($module) . "&section=images&album_id={$id}";
			}

			$Album = AlbumManager::getById($id);
			$Images = ImageInAlbumManager::getImages($id, true);

			$widgetContent = null;

			Application::assign([
				'album' => objectToArray($Album),
				'galleryImages' => objectToArray($Images),
				'GalleryShowLoadButton' => count($Images) < ImageInAlbumManager::count(),
				'PhotoGalleryAdminImagesLink' => $AdminLink
			]);

			if (!User::isAdmin() && $Album->isHidden()) {
				$widgetContent = '';
			} else {
				$widgetContent = Application::getContent('modules', "{$module}/tpl/widget");
			}

			$content = preg_replace(
				"#\[photogallery={$id}]#",
				$widgetContent,
				$content
			);
		}

		return $content;
	}
}
