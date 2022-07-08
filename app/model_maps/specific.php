<?php
// карта дополнительных компонентов
return [
    // компоненты "specific"
    'Account'                    => 'utils_specific/Account',
    'FormSender'                 => 'utils_specific/FormSender',
    'FormCallbackSender'         => 'utils_specific/FormCallbackSender',
    'FormCallbackOnBannerSender' => 'utils_specific/FormCallbackOnBannerSender',
    'LessSimpleXMLElement'       => 'utils_specific/LessSimpleXMLElement',

    'CMiniBanner' => 'entities_specific/CMiniBanner',
    'CMiniBannerMeta' => 'entities_specific/CMiniBannerMeta',
    'MiniBannerManager' => 'services_specific/MiniBannerManager',

    // модуль "specific"
    //'CClassTemplateMeta' => 'entities_specific/CClassTemplateMeta',
    //'CClassTemplate' => 'entities_specific/CClassTemplate',
    //'ClassTemplateManager' => 'services_specific/ClassTemplateManager',
    //'ClassTemplateNotFoundEx' => 'exceptions_specific/ClassTemplateNotFoundEx',
];
