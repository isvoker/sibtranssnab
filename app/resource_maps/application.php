<?php
// карта базовых ресурсов CMF AEngine
return [
	'css' => [
		'admin' => ['path' => 'admin.css'],
        'buttons' => ['path' => 'buttons.css'],
        'common' => ['path' => 'common.css'],
        'bootstrap.min' => ['path' => 'bootstrap.min.css'],
		'flexbox' => ['path' => 'flexbox.min.css'],
		'form' => ['path' => 'form.css'],
		'normalize' => ['path' => 'normalize.css'],

		'ext/jquery.fancybox' => ['path' => 'ext/fancybox/jquery.fancybox.min.css'],
		'ext/jquery.jgrowl' => ['path' => 'ext/jquery.jgrowl.css'],
		'ext/jquery.paginator' => ['path' => 'ext/jquery.paginator.css'],
		'ext/jquery.uitotop' => ['path' => 'ext/jquery.uitotop.css'],
		'ext/datetimepicker' => ['path' => 'ext/jquery.datetimepicker.min.css'],
		'ext/owl-carousel' => ['path' => 'ext/owl.carousel.min.css'],
		'ext/elegant-icons' => ['path' => 'ext/elegant-icons.css']
	],

	'js' => [
		'jquery' => [
			'path' => 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js',
			'isAbsolutePath' => true
		],
		'jquery-migrate' => [
			'path' => 'https://code.jquery.com/jquery-migrate-3.3.2.min.js',
			'isAbsolutePath' => true
		],

		'admin' => ['path' => 'admin.js'],
        'admin_guard' => ['path' => 'admin_guard.js'],
        'admin_pages' => ['path' => 'admin_pages.js'],
        'admin_seo' => ['path' => 'admin_seo.js'],
        'admin_users' => ['path' => 'admin_users.v20210518a.js'],
        'dictionaries-edit' => ['path' => 'dictionaries-edit.js'],
		'dictionaries-init' => ['path' => 'dictionaries-init.js'],
		'entity-edit' => ['path' => 'entity-edit.js'],
		'entity-edit_btns' => ['path' => 'entity-edit_btns.js'],
		'history' => ['path' => 'history.js'],
        'sensei-core' => ['path' => 'sensei-core.js'],
        'sensei-ui' => ['path' => 'sensei-ui.js'],
        'sensei-forms' => ['path' => 'sensei-forms.js'],
        'sensei-form' => ['path' => 'sensei-form.js'],
        'sensei-ui-extra' => ['path' => 'sensei-ui-extra.js'],

        'ext/sensei-easyui' => ['path' => 'ext/sensei-easyui.min.js'],
		'ext/jquery.blockUI' => ['path' => 'ext/jquery.blockUI.min.js'],
		'ext/jquery.easing' => ['path' => 'ext/jquery.easing.min.js'],
		'ext/jquery.fancybox' => ['path' => 'ext/jquery.fancybox.min.js'],
		'ext/jquery.form' => ['path' => 'ext/jquery.form.min.js'],
		'ext/jquery.jgrowl' => ['path' => 'ext/jquery.jgrowl.min.js'],
		'ext/jquery.paginator' => ['path' => 'ext/jquery.paginator.min.js'],
		'ext/jquery.uitotop' => ['path' => 'ext/jquery.uitotop.min.js'],
		'ext/jsencrypt' => ['path' => 'ext/jsencrypt.min.js'],
		'ext/polyfills' => ['path' => 'ext/polyfills.min.js'],
        'ext/jscolor.min' => ['path' => 'ext/jscolor.min.js'],
        'ext/jquery.ui' => ['path' => 'ext/jquery-ui.min.js'],
		'ext/datetimepicker' => ['path' => 'ext/jquery.datetimepicker.full.min.js'],
        'ext/owl-carousel' => ['path' => 'ext/owl.carousel.min.js'],
        'ext/jquery-vide' => ['path' => 'ext/jquery.vide.js'],

		'ckeditor' => [
			'path' => '/addons/ckeditor/ckeditor.js',
			'isAbsolutePath' => true
		],
		'ckfinder' => [
			'path' => '/addons/ckfinder/ckfinder.js',
			'isAbsolutePath' => true
		]
	]
];
