<?php
/**
 * События, происходящие на отдельных шаблонах
 */

Application::onBeforePageBlockLoaded(static function() {
    StaticResourceImporter::addComponent('specific');
    StaticResourceImporter::css('bootstrap.min');
    StaticResourceImporter::css('ext/owl-carousel');
    StaticResourceImporter::css('ext/elegant-icons');
    StaticResourceImporter::js('ext/owl-carousel');
    StaticResourceImporter::js('ext/jquery-vide');

    StaticResourceImporter::css('specific/style');

    StaticResourceImporter::js('sensei-ui-extra');
    StaticResourceImporter::js('specific/script');

    if (Application::isMobileSite()) {
        StaticResourceImporter::css('specific/style.m');
        StaticResourceImporter::js('specific/script.m');
        $tplNameSuffix = '.m';
    } else {
        StaticResourceImporter::css('specific/style.d');
        $tplNameSuffix = '';
    }

    define('TPL_NAME_SUFFIX', $tplNameSuffix);

    /** Проверка "правильности" браузера пользователя */
    if (Cfg::BROWSER_CHECKING_IS_ON) {
        $allowedBrowsers = [
            'Firefox' => [
                'name' => 'Mozilla Firefox',
                'link' => 'https://www.mozilla.org/ru/firefox/',
                'version' => 28
            ],
            'Opera' => [
                'name' => 'Opera',
                'link' => 'https://www.opera.com/ru/download',
                'version' => 12.1
            ],
            'Chrome' => [
                'name' => 'Chrome',
                'link' => 'https://www.google.com/chrome/',
                'version' => 21
            ],
            'IE' => [
                'name' => 'Internet Explorer',
                'link' => 'https://support.microsoft.com/ru-ru/help/17621/internet-explorer-downloads',
                'version' => 11
            ],
            'Edge' => [
                'name' => 'Microsoft Edge',
                'link' => 'https://www.microsoft.com/ru-ru/windows/microsoft-edge',
                'version' => 12
            ],
            'Safari' => [
                'name' => 'Safari',
                'link' => 'https://www.apple.com/safari/',
                'version' => 6.1
            ]
        ];
        $validateBrowser = Request::validUserBrowser($allowedBrowsers);
        if ($validateBrowser['answer'] !== 'good') {
            Application::assign('validBrowser', $validateBrowser);
        }
    }

    Application::plugRegisteredWidgets();
});

Application::onPageBlockLoaded(static function() {

    Application::assign([
	    'customHtml' => [
		    'head'   => SiteOptions::get('metadata'),
		    'footer' => SiteOptions::get('user_html')
	    ],
        'breadcrumbs'     => Application::getWidget('special', 'breadcrumbs'),
        'header_login'    => Application::getWidget('account', 'login-user'),
        'ya_counter_id'   => (int)SiteOptions::get('counter_id'),
        'THEME_COLOR_1'   => SiteOptions::get('theme_color_1'),
        'THEME_COLOR_2'   => SiteOptions::get('theme_color_2'),
        'contact_phone'   => SiteOptions::get('cont_phone'),
        'MainNavigator'   => Application::getWidget('menu', 'MainNavigator'),
        'FooterNavigator' => Application::getWidget('menu', 'FooterNavigator'),

        'HTML_BLOCK_HEADER_TITLE' => Application::getTextBlock('HTML_BLOCK_HEADER_TITLE'),
        'HTML_BLOCK_ADDRESS'      => Application::getTextBlock('HTML_BLOCK_ADDRESS'),
        'HTML_BLOCK_PHONES'       => Application::getTextBlock('HTML_BLOCK_PHONES'),
        'HTML_BLOCK_FOOTER_TITLE' => Application::getTextBlock('HTML_BLOCK_FOOTER_TITLE')
    ]);
});
