<?php

if (!User::isLoggedIn()) {
    Response::redirect(Cfg::URL_ACCOUNT_LOGIN);
}

$User = User::getEntity(
    (new ObjectOptions())->setForOutput()
);

Application::assign([
    'user' => $User->getFieldsForOutput()
]);

Application::showContent('account', 'account');
