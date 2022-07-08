<?php

if (User::isLoggedIn()) {
    Response::redirect(Cfg::URL_AFTER_LOGIN);
}

$token = Request::getVar('t', 'string');

StaticResourceImporter::js('ext/jsencrypt');
StaticResourceImporter::js('sensei-form');

if ($token) {
    Application::assign(['token' => $token]);
    Application::showContent('account', 'password_reset');
} else {
    Application::showContent('account', 'password_reset_request');
}
