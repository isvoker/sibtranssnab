
<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'CfgBase.php';

/**
 * Project configuration.
 */
class CfgProject extends CfgBase
{
	public const SMARTY_TEMPLATE_DIR = CfgBase::SMARTY_TEMPLATE_DIR + [
	    'specific' => self::DIR_VIEW . 'specific' . self::DS . 'tpl',
		'modules'  => self::DIR_ROOT . 'modules',
	];

    public const IMAGE_CACHE_DIR_WEB = '/files/cache/';
    public const DEFAULT_IMAGE_WEB = '/static/img/inner/no-photo.png';
}
