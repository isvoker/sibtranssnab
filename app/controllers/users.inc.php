<?php
/* controller "users" */
try {
    DBCommand::begin();

	switch ($action) {
        case 'auth':
            $fields = Request::getVar('fields', 'string');

            if (!$fields) {
                break;
            }

            $fields = getFormDataFromJson($fields, false);

            $data = Ajax::getDataOk();

			try {
				$data['redirect'] = User::authentication(
					$fields['login'] ?? '',
					$fields['password'] ?? ''
				);
			} catch (UserEx $E) {
				$data = Ajax::getDataError($E);
			}

            break;

        case 'singup':
            $fields = Request::getVar('fields', 'string');

            if (!$fields) {
                break;
            }

			$fields = getFormDataFromJson($fields, false);

			checkCaptcha($fields['captcha_code'] ?? '');
            unset($fields['captcha_code']);

            Account::create($fields, [Cfg::GRP_REGISTERED]);

			$data = Ajax::getDataOk();
            $data['msg'] = 'Аккаунт успешно создан';
            $data['redirect'] = Cfg::URL_ACCOUNT_LOGIN;

			break;

        case 'updateAccount':
            $fields = Request::getVar('fields', 'string');

            if (!$fields) {
                break;
            }

            $fields = getFormDataFromJson($fields, false);

            Account::update($fields);

            $data = Ajax::getDataOk();
            $data['msg'] = 'Данные успешно обновлены';

            break;

        case 'deleteAccount':
            Account::delete();

            $data = Ajax::getDataOk();
            $data['msg'] = 'Аккаунт успешно удалён';

            break;

        case 'passwordResetRequest':
            $fields = Request::getVar('fields', 'string', '');
            $fields = getFormDataFromJson($fields, false);

            if (
                !is_string($fields['captcha_code'] ?? null)
                || !is_string($fields['login'] ?? null)
            ) {
                break;
            }

            checkCaptcha($fields['captcha_code']);

            $userEmail = Account::passwordResetRequest($fields['login']);
            $userEmail = depersonalizeEmail($userEmail);

            $data = Ajax::getDataOk();
            $data['msg'] = "На адрес {$userEmail} отправлено сообщение с дальнейшими инструкциями";

            break;

        case 'passwordReset':
            $fields = Request::getVar('fields', 'string', '');
            $fields = getFormDataFromJson($fields, false);

            if (
                !is_string($fields['token'] ?? null)
                || !is_string($fields['password'] ?? null)
            ) {
                break;
            }

            Account::passwordReset($fields['token'], $fields['password']);

            $data = Ajax::getDataOk();
            $data['msg'] = 'Пароль успешно изменён';
            $data['redirect'] = Cfg::URL_ACCOUNT_LOGIN;

            break;

        case 'checkLogin':
            $login = Request::getVar('login', 'string');

            if (!$login) {
                break;
            }

            $data = Ajax::getDataOk();
            $data['isFree'] = !UserManager::loginUsed($login);

            break;

		default:
			break;
	}

    DBCommand::commit();
} catch (Throwable $E) {
	DBCommand::rollback();
	$data = Ajax::getDataError($E);
}
