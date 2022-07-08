<?php

if (!SiteOptions::get('allow_registration')) {
    Response::redirect(Cfg::URL_AFTER_LOGOUT);
}

if (User::isLoggedIn()) {
    Response::redirect(Cfg::URL_AFTER_LOGIN);
}

$token = Request::getVar('t', 'string', '');

if ($token) {
    Application::assign([
        'isActivated' => Account::activate($token)
    ]);
    Application::showContent('account', 'activation');
} else {
    StaticResourceImporter::js('ext/jsencrypt');
    StaticResourceImporter::js('sensei-form');

    Application::showContent('account', 'signup');
}
