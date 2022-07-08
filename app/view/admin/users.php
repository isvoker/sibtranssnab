<?php

EasyUILoader::putExtensions(['edatagrid']);
StaticResourceImporter::js('ext/jsencrypt');
StaticResourceImporter::js('admin_users');

Application::assign('historyEnabled', Cfg::HISTORY_IS_ON);
Application::showContent('admin', 'users');
