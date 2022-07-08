<?php

//ClassLoader::loadClass('SeoToponymManager');

/**
 * Class Sitemap.
 *
 * Работа с файлами Sitemap в XML-формате.
 *
 * @see https://www.sitemaps.org/protocol.html
 *
 * @author Dmitry Lunin
 */
class Sitemap
{
	/** Максимальное количество URL в одном файле Sitemap */
    protected const MAX_URLS_IN_MAP = 50000;

	/**
	 * Кодировка XML-файла
	 *
	 * @var string
	 */
    protected static $encoding = Cfg::CHARSET;

	/**
	 * Список доменов, для которых надо создать карту сайта,
	 * вида ['domain.name' => 'domain-prefix'].
	 *
	 * @var array
	 */
    protected static $domains = [];

	/**
	 * Содержимое карты сайта для каждого из доменов
	 * вида ['domain-prefix' => 'content'].
	 *
	 * @var array
	 */
    protected static $contents = [];

    /**
     * Маскирование символов в соответствии с RFC-3986, RFC-3987 и XML-стандартом.
     *
     * @param   string  $str  Значение атрибута для обработки
     * @return  string
     */
    protected static function maskingCharacters(string $str): string
    {
        $replace = [
            '&' => '&amp;',
            "'" => '&apos;',
            '"' => '&quot;',
            '>' => '&gt;',
            '<' => '&lt;'
        ];

        return str_replace(array_keys($replace), array_values($replace), $str);
    }

	/** Определение списка доменов */
    protected static function determineDomains(): void
	{
		$scheme = Request::isSecureConnection() ? 'https://' : 'http://';

		$mainDomain = Request::getServerName(false);
		self::$domains = [ $scheme . $mainDomain => '' ];

		//$altDomainPrefixes = SeoToponymManager::getAltDomainPrefixes();
		//foreach ($altDomainPrefixes as $prefix) {
		//	self::$domains[ "{$scheme}{$prefix}.{$mainDomain}" ] = "{$prefix}.";
		//}

		if (Cfg::MOBILE_VERSION_IS_ON === true) {
			$mainDomain = Cfg::MOBILE_DOMAIN_PREFIX . $mainDomain;
			self::$domains[ $scheme . $mainDomain ] = Cfg::MOBILE_DOMAIN_PREFIX;

			//foreach ($altDomainPrefixes as $prefix) {
			//	self::$domains[ "{$scheme}{$prefix}.{$mainDomain}" ] = "{$prefix}." . Cfg::MOBILE_DOMAIN_PREFIX;
			//}
		}
	}

    /**
     * Получение списка элементов основного содержимого.
     * По умолчанию - Страницы сайта.
     *
     * @return array
     */
    protected static function getItems(): array
    {
        $pages = DBCommand::select([
            'select' => [[
                'loc' => 'full_path',
                'lastmod' => DBQueryBuilder::conditional(
                    DBCommand::qC('updated_at'),
                    DBCommand::qC('updated_at'),
                    DBCommand::qC('created_at')
                )
            ]],
            'from'   => DBCommand::qC( CPageMeta::getDBTable() ),
            'where'  => 'in_map = 1 AND is_public = 1'
        ]);

        $addons = Extender::call('Sitemap::getItems');

        return $addons
            ? array_merge($pages, $addons)
            : $pages;
    }

    /**
     * Генерация содержимого карты сайта в XML-формате на основе записей,
     * возвращаемых [[Sitemap::getItems()]].
     */
    protected static function makeXmlContent(): void
	{
		foreach (self::$domains as $domain => $prefix) {
			self::$contents[ $prefix ] =
			'<?xml version="1.0" encoding="' . self::$encoding . '"?>' . "\n"
				. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
						. 'xmlns:xhtml="http://www.w3.org/1999/xhtml">';
		}

		foreach (self::getItems() as $key => $page) {
			if ($key > self::MAX_URLS_IN_MAP) {
				break;
			}

			$page['loc'] = self::maskingCharacters($page['loc']);
			if (isset($page['lastmod'])) {
				$page['lastmod'] = Time::get(DATE_ATOM, $page['lastmod']);
			}

			foreach (self::$domains as $domain => $prefix) {
				self::$contents[$prefix] .= "<url><loc>{$domain}{$page['loc']}</loc>";

				//if (DETERMINE_SITE_VERSION === true && $isNotMobileDomain) {
				//	$page['alternate'][] = [
				//		'type' => 'media',
				//		'condition' => 'only screen and (max-width:900px)',
				//		'href' => $mobileDomain . $page['loc']
				//	];
				//}

				//if (!empty($page['alternate'])) {
				//	foreach ($page['alternate'] as $alt) {
				//		self::$contents[ prefix ] .=
				//		"<xhtml:link rel=\"alternate\" {$alt['type']}=\"{$alt['condition']}\" href=\"{$alt['href']}\"/>";
				//	}
				//}

				if (isset($page['lastmod'])) {
					self::$contents[ $prefix ] .= "<lastmod>{$page['lastmod']}</lastmod>";
				}

				if (isset($page['changefreq'])) {
					self::$contents[ $prefix ] .= "<changefreq>{$page['changefreq']}</changefreq>";
				}

				if (isset($page['priority'])) {
					// NOTE: можно ставить priority=1 для Главной, а для остальных страниц - 0.6
					self::$contents[ $prefix ] .= "<priority>{$page['priority']}</priority>";
				}

				self::$contents[ $prefix ] .= '</url>';
			}
		}

		foreach (self::$domains as $domain => $prefix) {
			self::$contents[ $prefix ] .= "</urlset>\n";
		}
	}

	/** Сохранение файлов карты сайта */
    protected static function saveFiles(): void
	{
		foreach (self::$domains as $domain => $prefix) {
			$filePath = Cfg::DIR_FILES . "sitemap.{$prefix}xml";
            FsFile::make($filePath, self::$contents[ $prefix ]);
            FsArchive::makeGZ($filePath, "{$filePath}.gz");
		}
		self::$contents = [];
	}

	/**
	 * Изменение кодировки XML-файла
	 *
	 * @param  string  $encoding
	 */
	public static function setEncoding(string $encoding): void
	{
		self::$encoding = Html::qSC($encoding);
	}

	/** Создание файлов Sitemap в соответствии с настройками сайта */
	public static function make(): void
	{
		if (!SiteOptions::get('sitemap_enabled')) {
			return;
		}

		self::determineDomains();
		self::makeXmlContent();
		self::saveFiles();
	}

	/** Удаление всех файлов Sitemap */
	public static function delete(): void
	{
		self::determineDomains();

		foreach (self::$domains as $domain => $prefix) {
			$path = Cfg::DIR_FILES . "sitemap.{$prefix}xml";
			if (FsFile::isExists($path) && is_writable($path)) {
				unlink($path);
			}

			$path .= '.gz';
			if (FsFile::isExists($path) && is_writable($path)) {
				unlink($path);
			}
		}
	}
}
