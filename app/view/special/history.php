<?php

StaticResourceImporter::addComponent('specific');

StaticResourceImporter::css('specific/style');

if (!empty($_REQUEST)) {
    StaticResourceImporter::js('history');
    Application::showContent('special', 'history');
}
