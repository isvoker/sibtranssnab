<?php

if (!User::isLoggedIn()) {
    Response::redirect(Cfg::URL_ACCOUNT_LOGIN);
}

StaticResourceImporter::js('ext/jsencrypt');
StaticResourceImporter::js('sensei-form');


$User = User::getEntity(
    (new ObjectOptions())->setForOutput()
);

Application::assign([
    'user' => $User->getFieldsForOutput()
]);
Application::showContent('account', 'edit');
