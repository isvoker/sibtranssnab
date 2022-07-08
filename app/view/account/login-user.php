<?php

try {
    $Action = 'MODULE';

    if (!empty($this->props['is_widget'])) {
        $Action = 'WIDGET';
    }

    $allowRegistration = SiteOptions::get('allow_registration');

    switch ($Action) {
        case 'WIDGET':
            if (!$allowRegistration) {
                break;
            }

            Application::showContent('account', 'login-widget');

            break;

        case 'MODULE':
        default:
            if (User::isLoggedIn()) {
                Response::redirect(Cfg::URL_AFTER_LOGIN);
            }

            if (!$allowRegistration) {
                Response::redirect(Cfg::URL_AFTER_LOGOUT);
            }

            StaticResourceImporter::js('ext/jsencrypt');
            StaticResourceImporter::js('sensei-form');

            Application::showContent('account', 'login-user');

            break;
    }

} catch (Exception $Ex) {
    Logger::registerException($Ex);
}
