<?php

/**
 * Управление объектами класса [[CImageInAlbum]].
 *
 * @author Lunin Dmitriy
 */
class ImageInAlbumManager extends EntityManager implements EntityManagerInterface
{
	use PositProcessorTrait;

	/**
	 * Максимальный размер загружаемого файла.
	 * 3_145_728 B = 3 MB.
	 */
	public const IMAGE_MAX_SIZE = 0x300000;

	/**
	 * @param array $dbRows
	 * @param  ?ObjectOptions $Options
	 * @return  array
	 * @see     EntityManager::baseToObjects()
	 */
	public static function toObjects(array $dbRows, ObjectOptions $Options = null): array
	{
		return parent::baseToObjects($dbRows, CImageInAlbum::class, $Options);
	}

	/**
	 * @param int $id
	 * @param  ?ObjectOptions $Options
	 * @return  CImageInAlbum
	 * @see     EntityManager::baseGetById()
	 */
	public static function getById(int $id, ObjectOptions $Options = null): AbstractEntity
	{
		return parent::baseGetById(CImageInAlbum::class, ImageInAlbumNotFoundEx::class, $id, $Options);
	}

	/**
	 * @param  ?FetchBy $FetchBy
	 * @param  ?FetchOptions $FetchOptions
	 * @param  ?ObjectOptions $ObjectOptions
	 * @return  array
	 * @see     EntityManager::baseFetch()
	 */
	public static function fetch(
		FetchBy $FetchBy = null,
		FetchOptions $FetchOptions = null,
		ObjectOptions $ObjectOptions = null
	): array
	{
		return parent::baseFetch(
			CImageInAlbumMeta::getInstance(),
			$FetchBy,
			$FetchOptions,
			$ObjectOptions
		);
	}

	public static function getImages(int $albumId, bool $is_widget = false, int $part = 0): array
	{
		return self::fetch(
			(new FetchBy())
				->and(['album_id' => $albumId]),
			(new FetchOptions())
				->setLimit( self::getLimit($is_widget) )
				->setPage($part)
				->setCount(),
			(new ObjectOptions())
				->setForOutput()
				->setWithExtraData()
		);
	}

	public static function getCountInAlbum(int $albumId)
	{
		return (int)DBCommand::select([
			'select' => 'COUNT(*)',
			'from' => CImageInAlbumMeta::getDBTable(),
			'where' => "album_id={$albumId}"
		], DBCommand::OUTPUT_FIRST_CELL);
	}

	/**
	 * Изменение позиции изображения внутри альбома.
	 *
	 * @param CImageInAlbum $Img Изображение
	 * @param int $targetId ID изображения, относительно которого задаётся позиция
	 * @param string $point Целевое положение: 'prepend'|'append'|'top'|'bottom'
	 */
	public static function setImagePosit(CImageInAlbum $Img, int $targetId, string $point): void
	{
		$NewImg = clone $Img;
		$NewImg->extraData['posit_target'] = $targetId;
		$NewImg->extraData['posit_point'] = $point;
		self::setPosit($NewImg);
		self::update($Img, $NewImg);
	}

	public static function getLimit(bool $is_widget = false): int
	{
		if ($is_widget) {
			$limitSettingName = 'photos_per_page_in_widget';
		} else {
			$limitSettingName = 'photos_per_page';
		}

		return (int)BlockManager::getModuleSettings(PhotoGallery::class, $limitSettingName);
	}
}
