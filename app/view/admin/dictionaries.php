<?php

ClassLoader::loadClass('Dictionary');

if ($id = Request::getVar('id', 'numeric')) {
    EasyUILoader::putExtensions(['etree']);
    StaticResourceImporter::js('dictionaries-edit');

    $dicInfo = Dictionary::getDictionaryById($id);

    Application::assign('dictionary', $dicInfo);
} else {
    Application::assign('dictionaries', Dictionary::getDictionaries());
}

Application::showContent('admin', 'dictionaries');
