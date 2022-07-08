<?php
// карта дополнительных ресурсов
return [
	'css' => [ // /static/css/...
        'specific/style' => ['path' => 'specific/style.css'],
        'specific/style.d' => ['path' => 'specific/style.d.css'],
        'specific/style.m' => ['path' => 'specific/style.m.css'],
        'specific/submenu' => ['path' => 'specific/submenu.css'],

		'ext/slick' => ['path' => 'ext/slick/slick.min.css']
	],

	'js' => [ // /static/js/...
		'specific/special' => ['path' => 'specific/special.js'],
		'specific/script' => ['path' => 'specific/script.js'],
        'specific/script.m' => ['path' => 'specific/script.m.js'],

        'ext/inputmask' => ['path' => 'ext/jquery.inputmask.min.js'],
        'ext/slick' => ['path' => 'ext/slick.min.js'],
	]
];
