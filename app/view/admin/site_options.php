<?php

StaticResourceImporter::js('sensei-form');
StaticResourceImporter::js('admin_seo');

StaticResourceImporter::js("ext/jscolor.min");

$group = $this->props['group'];

Application::assign([
    'options' => SiteOptions::getDeclaration($group)
]);
Application::showContent('admin', "site_options_{$group}");
