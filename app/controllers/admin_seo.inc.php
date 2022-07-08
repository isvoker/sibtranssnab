<?php
/* controller "seo" */
try {
	switch ($action) {
		case 'updateSitemap':
			Sitemap::delete();
			Sitemap::make();

			$data = Ajax::getDataOk();

			break;

		case 'updateRobotsTxt':
			RobotsTxt::make();

			$data = Ajax::getDataOk();

			break;

		case 'disallowRobots':
			RobotsTxt::disallowRobots();

			$data = Ajax::getDataOk();

			break;

		case 'makeYml':
			$data = YmlMaker::makeYml()
				? Ajax::getDataOk()
				: ['status' => Response::STATUS_UNKNOWN_ERROR];

			break;

		default:
			break;
	}
} catch (Throwable $E) {
	DBCommand::rollback();
	$data = Ajax::getDataError($E);
}
