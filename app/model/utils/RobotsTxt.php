<?php

//ClassLoader::loadClass('SeoToponymManager');

/**
 * Class RobotsTxt.
 *
 * Работа с файлами robots.txt.
 *
 * @author Dmitry Lunin
 */
class RobotsTxt
{
	/** Шаблон типичного файла robots.txt */
    protected const TPL = 'robots.txt_tpl';

	/** Шаблон файла robots.txt, полностью запрещающего индексацию сайта */
    protected const TPL_NOINDEX = 'robots.txt_noindex';

	/** Паттерны, заменяемые в self::TPL на пользовательский контент */
    protected const PATTERN_DISALLOW = '/\{DISALLOW\}\n/';
    protected const PATTERN_CUSTOM_CONTENT = '/\{CUSTOM_CONTENT\}\n/';
    protected const PATTERN_SITEMAP = '/\{SITEMAP\}\n/';
    protected const PATTERN_HOST = '/\{HOST\}/';

	/**
	 * Список доменов, для которых надо создать файл robots.txt,
	 * вида ['domain.name' => 'domain-prefix'].
	 *
	 * @var array
	 */
    protected static $domains = [];

	/**
	 * Содержимое файла robots.txt для каждого из доменов
	 * вида ['domain-prefix' => 'content'].
	 *
	 * @var array
	 */
    protected static $contents = [];

	/** Определение списка доменов */
    protected static function determineDomains(): void
	{
		$mainDomain = Request::getServerName(false);
		self::$domains = [ $mainDomain => '' ];

		//$altDomainPrefixes = SeoToponymManager::getAltDomainPrefixes();
		//foreach ($altDomainPrefixes as $prefix) {
        //	self::$domains[ "{$prefix}.{$mainDomain}" ] = "{$prefix}.";
		//}

		if (Cfg::MOBILE_VERSION_IS_ON === true) {
			$mainDomain = Cfg::MOBILE_DOMAIN_PREFIX . $mainDomain;
			self::$domains[ $mainDomain ] = Cfg::MOBILE_DOMAIN_PREFIX;

			//foreach ($altDomainPrefixes as $prefix) {
            //	self::$domains[ "{$prefix}.{$mainDomain}" ] = "{$prefix}." . Cfg::MOBILE_DOMAIN_PREFIX;
			//}
		}
	}

    /**
     * Получение списка неиндексируемых Страниц.
     *
     * @return array
     */
    protected static function getProhibitedItems(): array
    {
        $pages = DBCommand::select([
            'select' => 'full_path',
            'from'   => DBCommand::qC( CPageMeta::getDBTable() ),
            'where'  => 'noindex = 1'
        ], DBCommand::OUTPUT_FIRST_COLUMN);

        $addons = Extender::call('RobotsTxt::getProhibitedItems');

        return $addons
            ? array_merge($pages, $addons)
            : $pages;
    }

	/** Формирование директив Disallow */
    protected static function setDisallowDirectives(): void
	{
		$disallow = '';
        foreach (self::getProhibitedItems() as $url) {
			$disallow .= "Disallow: {$url}\n";
		}

		foreach (self::$domains as $domain => $prefix) {
			self::$contents[ $prefix ] = preg_replace(
				self::PATTERN_DISALLOW,
				$disallow,
				self::$contents[ $prefix ]
			);
		}
	}

	/** Добавление пользовательского содержимого */
    protected static function setCustomContent(): void
	{
		$customContent = SiteOptions::get('robots_extra');
		if ($customContent) {
			$customContent .= "\n";
		}
		foreach (self::$domains as $domain => $prefix) {
			self::$contents[ $prefix ] = preg_replace(
				self::PATTERN_CUSTOM_CONTENT,
				$customContent,
				self::$contents[ $prefix ]
			);
		}
	}

	/** Формирование директивы Sitemap */
    protected static function setSitemapDirective(): void
	{
		if (SiteOptions::get('sitemap_enabled')) {
			$scheme = Request::isSecureConnection() ? 'https://' : 'http://';
			foreach (self::$domains as $domain => $prefix) {
				self::$contents[ $prefix ] = preg_replace(
					self::PATTERN_SITEMAP,
					"Sitemap: {$scheme}{$domain}/sitemap.xml.gz\n",
					self::$contents[ $prefix ]
				);
			}
		} else {
			foreach (self::$domains as $domain => $prefix) {
				self::$contents[ $prefix ] = preg_replace(
					self::PATTERN_SITEMAP,
					'',
					self::$contents[ $prefix ]
				);
			}
		}
	}

	/** Формирование директивы Host */
    protected static function setHostDirective(): void
	{
		$scheme = Request::isSecureConnection() ? 'https://' : '';
		foreach (self::$domains as $domain => $prefix) {
			self::$contents[ $prefix ] = preg_replace(
				self::PATTERN_HOST,
				"Host: {$scheme}{$domain}",
				self::$contents[ $prefix ]
			);
		}
	}

	/** Создание типичных файлов robots.txt */
	public static function make(): void
	{
		$tplContent = file_get_contents(Cfg::DIR_FILES . self::TPL);
		if (!$tplContent) {
            throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, self::TPL );
		}

		self::determineDomains();

		foreach (self::$domains as $domain => $prefix) {
			self::$contents[ $prefix ] = $tplContent;
		}

		self::setDisallowDirectives();
		self::setCustomContent();
		self::setSitemapDirective();
		self::setHostDirective();

		foreach (self::$domains as $domain => $prefix) {
            FsFile::make(Cfg::DIR_FILES . "robots.{$prefix}txt", self::$contents[ $prefix ]);
		}
		self::$contents = [];
	}

	/** Создание файлов robots.txt, полностью запрещающих индексирование сайта */
	public static function disallowRobots(): void
	{
		self::determineDomains();
		$result = true;

		foreach (self::$domains as $domain => $prefix) {
			$result = $result && copy(
			    Cfg::DIR_FILES . self::TPL_NOINDEX,
				Cfg::DIR_FILES . "robots.{$prefix}txt"
			);
		}

		if (!$result) {
            throw new FilesystemEx( FilesystemEx::FILE_IS_NOT_READABLE, self::TPL_NOINDEX );
		}
	}

	/** Удаление всех файлов robots.txt */
	public static function delete(): void
	{
        self::determineDomains();

		foreach (self::$domains as $domain => $prefix) {
			$path = Cfg::DIR_FILES . "robots.{$prefix}txt";
			if (FsFile::isExists($path) && is_writable($path)) {
				unlink($path);
			}
		}
	}
}
