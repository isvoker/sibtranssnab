<?php
/* controller "cms" */
try {
	switch ($action) {
		case 'getPublicKey':
			$data = Ajax::getDataOk();
			$data['key'] = str_replace(["\r", "\n"], '', Cfg::RSA_PUBLIC_KEY);

			break;

		case 'jsErrorReport':
			if (!Cfg::JS_ERRORS_LOGGING_IS_ON) {
				$data = Ajax::getDataOk();
				break;
			}

            $error = Request::getVar('error', 'array');

			if (
                !$error
				|| !isset(
					$error['href'],
					$error['msg'],
					$error['file'],
					$error['line'],
					$error['column']
				)
			) {
				break;
			}

			$error['ip'] = Request::getUserIP();
			$error['ua'] = Request::getUserAgent();

			$error = array_map(static function ($str) {
				return is_string($str) ? truncate($str, 512) : '-';
			}, $error);

			Logger::error(
				'JS report',
				"{$error['href']}\n{$error['msg']}\n"
					. "{$error['file']}:{$error['line']}:{$error['column']}\n"
					. "{$error['ip']} \"{$error['ua']}\"",
                Logger\LogToFile::HANDLER
			);

			$data = Ajax::getDataOk();

			break;

        case 'setOptions':
	        if (!User::isAdmin()) {
		        throw new AccessDeniedEx();
	        }

            $fields = Request::getVar('fields', 'string');

            if (!$fields) {
                break;
            }

            $fields = getFormDataFromJson($fields, false);

            DBCommand::begin();

            SiteOptions::set($fields);

            $data = Ajax::getDataOk();
            $data['msg'] = 'Настройки успешно сохранены';

            DBCommand::commit();

            break;

        case 'getBlock':
            $ident = Request::getVar('ident', 'string');

            if (!$ident) {
                break;
            }

            $props = http_build_query(Request::getVar('props', 'array'));

            $data = Ajax::getDataOk();
            $data['block'] = Application::getBlock($ident, $props);

            break;

        case 'getTextBlock':
            $ident = Request::getVar('ident', 'string');

            if (!$ident) {
                break;
            }

            $data = Ajax::getDataOk();
            $data['block'] = Application::getTextBlock($ident, false);

            break;

		default:
			break;
	}
} catch (Throwable $E) {
	DBCommand::rollback();
	$data = Ajax::getDataError($E);
}
