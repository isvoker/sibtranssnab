<?php

$options = getopt(
	'l:p:',
	[
		'install', // первичная установка
		'run:'  // запуск произвольного скрипта
	]
);

if (
	!isset($options['install'])
	&& !isset($options['run'])
) {
	echo 'usage:', PHP_EOL,
		"  {$argv[0]} --install [-l \"\$adminLogin\"] [-p \"\$adminPass\"]", PHP_EOL,
		"  {$argv[0]} --run \$script", PHP_EOL,
		'for example:', PHP_EOL,
		"  {$argv[0]} --run cron", PHP_EOL;
	exit;
}

define('IS_CLI_MODE', true);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Cfg.php';

require Cfg::DIR_FUNCTIONS . 'functions.php';
require Cfg::DIR_MODEL . 'core' . Cfg::DS . 'ClassLoader.php';

ClassLoader::init();

Logger\LogToFile::addHandler();
Logger::init();

try {
	DBCommand::connect();
	DBCommand::begin();

	if (isset($options['install'])) {

		require __DIR__ . DIRECTORY_SEPARATOR . 'install.inc.php';

	} elseif ($options['run'] ?? null) {

		Application::run(['breadcrumbs' => false, 'mobile' => false, 'smarty' => false]);
		require __DIR__ . DIRECTORY_SEPARATOR . 'run' . DIRECTORY_SEPARATOR . $options['run'] . '.inc.php';

	}

	DBCommand::commit();
} catch (Throwable $E) {
	DBCommand::rollback();
	Logger::registerException($E);
    echo $E, PHP_EOL;
} finally {
	Logger::showExceptions();
}
