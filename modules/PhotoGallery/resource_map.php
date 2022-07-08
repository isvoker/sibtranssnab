<?php
$module = 'PhotoGallery';
return [
	'css' => [
		"{$module}/admin" => [
		    'path' => 'admin.css',
            'module' => $module
        ],
        "{$module}/style" => [
            'path' => 'style.css',
            'module' => $module
        ],
        "{$module}/style.m" => [
            'path' => 'style.m.css',
            'module' => $module
        ]
	],

	'js' => [
        "{$module}/admin" => [
            'path' => 'admin.js',
            'module' => $module
        ],
        "{$module}/action" => [
            'path' => 'action.js',
            'module' => $module
        ],

        'clipboard' => [
            'path' => 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js',
            'isAbsolutePath' => true
        ]
	]
];
