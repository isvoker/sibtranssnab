<?php

/**
 * Управление объектами класса [[CAlbum]].
 *
 * @author Lunin Dmitriy
 */
class AlbumManager extends EntityManager implements EntityManagerInterface
{
	use PositProcessorTrait;

	/**
	 * @see     EntityManager::baseToObjects()
	 * @param   array          $dbRows
	 * @param  ?ObjectOptions  $Options
	 * @return  array
	 */
	public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
	{
		return parent::baseToObjects($dbRows, CAlbum::class, $Options);
	}

	/**
	 * @see     EntityManager::baseGetById()
	 * @param   int            $id
	 * @param  ?ObjectOptions  $Options
	 * @return  CAlbum
	 */
	public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
	{
		return parent::baseGetById(CAlbum::class, AlbumNotFoundEx::class, $id, $Options);
	}

	/**
	 * @see     EntityManager::baseFetch()
	 * @param  ?FetchBy        $FetchBy
	 * @param  ?FetchOptions   $FetchOptions
	 * @param  ?ObjectOptions  $ObjectOptions
	 * @return  array
	 */
	public static function fetch(
		FetchBy $FetchBy = null,
		FetchOptions $FetchOptions = null,
		ObjectOptions $ObjectOptions = null
	): array {
		if (!CAlbumMeta::canWeDoIt(Action::UPDATE)) {
			$FetchBy === null && $FetchBy = new FetchBy();
			$FetchBy->and(['is_hidden' => false]);
		}

		return parent::baseFetch(
			CAlbumMeta::getInstance(),
			$FetchBy,
			$FetchOptions,
			$ObjectOptions
		);
	}

	public static function makeAlbumUrl(int $AlbumId)
	{
		$albumPageUrl = self::getPageWithAlbumUrl($AlbumId);

		if (empty($albumPageUrl)) {
			return false;
		}

		return "{$albumPageUrl}?album={$AlbumId}";
	}

	public static function getPageWithAlbumUrl(int $album_id): string
	{
		return (string)DBCommand::select([
			'select' => 'p.full_path',
			'from' => Cfg::DB_TBL_PREFIX . 'pages AS p',
			'join' => 'LEFT JOIN ' . CAlbumMeta::getDBTable() . ' AS a ON p.id = a.page_id',
			'where' => "a.id={$album_id}"
		], DBCommand::OUTPUT_FIRST_CELL);
	}

	public static function addAlbumsToBreadcrumbs(int $id): void
	{
		$albums = [];
		self::getParentAlbumsArray($id, $albums);

		$albums = array_reverse($albums);

		foreach ($albums as $album) {
			Application::addBreadcrumbs($album->name, AlbumManager::getPageWithAlbumUrl($album->id));
		}
	}

	public static function addAlbumsToAdminBreadcrumbs(int $id): void
	{
		$albums = [];
		self::getParentAlbumsArray($id, $albums);

		$albums = array_reverse($albums);

		foreach ($albums as $album) {
			Application::addBreadcrumbs(
				$album->name,
				BlockManager::getAdminBlockPath(PhotoGallery::MODULE_IDENT . "&section=albums&album_id={$album->id}"));
		}
	}

	private static function getParentAlbumsArray(int $AlbumId, array &$albums): void
	{
		$album = self::getById($AlbumId);

		$albums[] = $album;

		if (!empty($album->parent)) {
			self::getParentAlbumsArray($album->parent, $albums);
		}
	}

	public static function setAlbumPosit(int $id, int $posit): void
	{
		DBCommand::update(
			CAlbumMeta::getDBTable(),
			['posit' => $posit],
			"id={$id}"
		);
	}
}
