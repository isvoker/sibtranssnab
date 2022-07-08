<?php

Application::assign([
    'text_content' => Application::getPageTextContent(),
]);

Application::showContent('special', 'products' . TPL_NAME_SUFFIX);