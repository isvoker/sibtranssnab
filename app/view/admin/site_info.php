<?php

Application::assign('props', Benchmarker::getSiteInfo());
Application::showContent('admin', 'site_info');
