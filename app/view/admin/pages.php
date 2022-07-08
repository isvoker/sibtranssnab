<?php

StaticResourceImporter::js('admin_pages');

Application::assign('delWithSubPages', SiteOptions::get('delete_with_subpages'));
Application::showContent('admin', 'pages');
