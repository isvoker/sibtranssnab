<?php

if (User::isLoggedIn()) {
    Response::redirect(Cfg::URL_AFTER_LOGIN);
}

StaticResourceImporter::js('ext/jsencrypt');
StaticResourceImporter::js('sensei-form');

Application::showContent('account', 'login-admin');
