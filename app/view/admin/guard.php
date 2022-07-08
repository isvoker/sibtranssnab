<?php

StaticResourceImporter::js('admin_guard');

Application::assign([
	'userIP' => Request::getUserIP(),
	'failPeriod' => Guard::FAIL_PERIOD,
	'failThreshold' => Guard::FAIL_THRESHOLD,
	'banPeriod' => Guard::BANPERIOD,
	'banLimit' => Guard::BANLIMIT
]);
Application::showContent('admin', 'guard');
