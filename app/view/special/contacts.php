<?php

Application::assign([
    'text_content' => Application::getPageTextContent(),
    'HTML_BLOCK_CONTACT_MAP' => Application::getTextBlock('HTML_BLOCK_CONTACT_MAP')
]);

Application::showContent('special', 'contacts' . TPL_NAME_SUFFIX);
