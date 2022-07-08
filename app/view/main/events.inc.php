<?php
/**
 * Общие для всех шаблонов события
 */

Application::onBeforePageBlockLoaded(static function() {
    require 'required_import.inc.php';

    RTP::set('siteName', SiteOptions::get('site_name'));

    Application::importPageAttributes();

    Application::assign([
        'cfg' => [
            'charset' => Cfg::CHARSET,
            'button' => [
                'color' => Cfg::UI_BUTTONS_COLOR,
                'colorSubmit' => Cfg::UI_BUTTONS_SUBMIT_COLOR,
                'colorDelete' => Cfg::UI_BUTTONS_DELETE_COLOR
            ],
            'url' => [
                'login' => Cfg::URL_ACCOUNT_LOGIN,
                'logout' => Cfg::URL_ACCOUNT_LOGOUT,
                'accountEdit' => Cfg::URL_ACCOUNT_EDIT,
                'passwordReset' => Cfg::URL_ACCOUNT_RESET_PASSWORD,
                'signup' => Cfg::URL_ACCOUNT_SIGNUP,
                'account' => Cfg::URL_AFTER_LOGIN
            ]
        ],
        'session' => [
            'token' => Tokenizer::getSessionToken(),
            'user' => [
                'isAuth' => User::isLoggedIn(),
                'isAdmin' => User::isAdmin(),
                'name' => User::name()
            ]
        ]
    ]);
});

Application::onPageBlockLoaded(static function() {
    if (Cfg::MINIFY_IS_ON) {
        StaticResourceImporter::glueResources();
    }

    Application::assign([
        'RTP' => RTP::getAll(),
        'importedResources' => StaticResourceImporter::getResources()
    ]);
});
