<?php

/** Логин администратора */
$adminLogin = $options['l'] ?? 'admin';
/** Пароль администратора. Разрешено использовать только надёжный пароль! */
$adminPass = $options['p'] ?? Randomizer::getPasswordSmart();
$adminPassHash = Security::calculatePasswordHash($adminPass);
/** Email администратора */
$adminEmail = 'shevelev@web-ae.ru';
/** Идентификатор главной страницы админки */
$adminIdent = 'admin-panel';

$dbTables = [
	'dictionaries'              => Cfg::DB_TBL_PREFIX . 'dictionaries',
	'dictionary_values'         => Cfg::DB_TBL_PREFIX . 'dictionary_values',
	'groups'                    => Cfg::DB_TBL_PREFIX . 'groups',
	'guard_lockouts'            => Cfg::DB_TBL_PREFIX . 'guard_lockouts',
	'guard_log'                 => Cfg::DB_TBL_PREFIX . 'guard_log',
	'history'                   => Cfg::DB_TBL_PREFIX . 'history',
	'main_templates'            => Cfg::DB_TBL_PREFIX . 'main_templates',
	'mini_banners'              => Cfg::DB_TBL_PREFIX . 'mini_banners',
	'modules'                   => Cfg::DB_TBL_PREFIX . 'modules',
	'modules_settings'          => Cfg::DB_TBL_PREFIX . 'modules_settings',
	'modules_to_templates'      => Cfg::DB_TBL_PREFIX . 'modules_to_templates',
	'pages'                     => Cfg::DB_TBL_PREFIX . 'pages',
	'page_permissions'          => Cfg::DB_TBL_PREFIX . 'page_permissions',
	'registered_triggers'       => Cfg::DB_TBL_PREFIX . 'registered_triggers',
	'site_options'              => Cfg::DB_TBL_PREFIX . 'site_options',
	'text_blocks'               => Cfg::DB_TBL_PREFIX . 'text_blocks',
	'users'                     => Cfg::DB_TBL_PREFIX . 'users',
	'user_groups'               => Cfg::DB_TBL_PREFIX . 'user_groups'
];

DBCommand::disableConstraints();

foreach ($dbTables as $tbl => $name) {
	DBCommand::dropTable($name);
}

$queries = [
// Table structure for "dictionaries"
"CREATE TABLE `{$dbTables['dictionaries']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `alias` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `revision` smallint(2) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `IX_alias` (`alias`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Словари'",

// Table structure for "dictionary_values"
"CREATE TABLE `{$dbTables['dictionary_values']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dictionary_id` int(10) UNSIGNED NOT NULL,
  `parent` int(10) UNSIGNED DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `posit` smallint(2) UNSIGNED DEFAULT NULL,
  `deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IX_dictionary_id` (`dictionary_id`),
  KEY `IX_deleted` (`deleted`) USING BTREE,
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_dictionary_values__dictionaries` FOREIGN KEY (`dictionary_id`)
    REFERENCES `{$dbTables['dictionaries']}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Содержимое словарей'",

// Table structure for "groups"
"CREATE TABLE `{$dbTables['groups']}` (
  `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_id_name` (`id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Группы пользователей'",

// Records of "groups"
"INSERT INTO `{$dbTables['groups']}` (`id`, `name`, `description`) VALUES
  (1, '" . Cfg::GRP_GUEST . "', 'Гость'),
  (2, '" . Cfg::GRP_ADMINS . "', 'Администратор'),
  (3, '" . Cfg::GRP_DEVELS . "', 'Разработчик'),
  (4, '" . Cfg::GRP_REGISTERED . "', 'Зарегистрированный')",

// Table structure for "guard_lockouts"
"CREATE TABLE `{$dbTables['guard_lockouts']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `time_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_end` timestamp NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IX_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Баны IP'",

// Table structure for "guard_log"
"CREATE TABLE `{$dbTables['guard_log']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` smallint(5) UNSIGNED NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) DEFAULT NULL,
  `user_login` varchar(255) DEFAULT NULL,
  `data` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IX_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Логи'",

// Table structure for "history"
"CREATE TABLE `{$dbTables['history']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `is_user_history` tinyint(1) UNSIGNED NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IX_user_id` (`user_id`),
  KEY `IX_is_user_history` (`is_user_history`),
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_history__users` FOREIGN KEY (`user_id`)
    REFERENCES `{$dbTables['users']}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='История'",

// Table structure for "main_templates"
"CREATE TABLE `{$dbTables['main_templates']}` (
  `ident` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `is_system` tinyint(1) UNSIGNED NOT NULL,
  `posit` smallint(2) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`ident`),
  KEY `IX_is_system` (`is_system`),
  KEY `IX_posit` (`posit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Главные шаблоны страниц'",

// Records of "main_templates"
"INSERT INTO `{$dbTables['main_templates']}` (`ident`, `name`, `is_system`, `posit`) VALUES
  ('text', 'Текстовый контент', 0, 1),
  ('two_col', 'Текстовый контент с колонкой', 0, 2),
  ('standard', 'Стандартная страница', 0, 3),
  ('100vw', 'На всю ширину экрана', 0, 4),
  ('front', 'Главная страница', 0, 5),
  ('plain', 'Без оформления', 1, 6),
  ('empty', 'Пустой', 1, 7),
  ('admin', 'Административный раздел', 1, 8)",

// Table structure for "mini_banners"
"CREATE TABLE `{$dbTables['mini_banners']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_target_blank` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Мини баннеры'",

// Table structure for "modules"
"CREATE TABLE `{$dbTables['modules']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ident` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `is_page` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_widget` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `IX_ident` (`ident`),
  KEY `IX_is_page` (`is_page`),
  KEY `IX_is_widget` (`is_widget`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Модули'",

// Records of "modules"
"INSERT INTO `{$dbTables['modules']}` (`ident`, `name`, `is_page`, `is_widget`) VALUES
  ('html', 'Статический HTML', 1, 0)",

// Table structure for "modules_settings"
"CREATE TABLE `{$dbTables['modules_settings']}` (
  `module_ident` varchar(64) NOT NULL,
  `ident` varchar(128) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`module_ident`, `ident`),
  KEY `IX_module_ident` (`module_ident`),
  KEY `IX_ident` (`ident`),
  CONSTRAINT `FK_modules_settings__modules` FOREIGN KEY (`module_ident`)
    REFERENCES `{$dbTables['modules']}` (`ident`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Параметры модулей'",

// Table structure for "modules_to_templates"
"CREATE TABLE `{$dbTables['modules_to_templates']}` (
  `template_ident` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `module_ident` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`template_ident`, `module_ident`),
  KEY `IX_module_ident` (`module_ident`),
  CONSTRAINT `FK_modules_to_templates__main_templates` FOREIGN KEY (`template_ident`)
    REFERENCES `{$dbTables['main_templates']}` (`ident`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_modules_to_templates__modules` FOREIGN KEY (`module_ident`)
    REFERENCES `{$dbTables['modules']}` (`ident`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Связь виджетов с шаблонами'",

// Table structure for "pages"
"CREATE TABLE `{$dbTables['pages']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent` int(10) UNSIGNED NOT NULL,
  `posit` smallint(2) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(128) NOT NULL,
  `ident` varchar(128) NOT NULL,
  `full_path` varchar(255) NOT NULL,
  `h1` varchar(255) DEFAULT NULL,
  `main_template` varchar(32) NOT NULL,
  `module` varchar(32) NOT NULL DEFAULT 'html',
  `content` mediumtext DEFAULT NULL,
  `content_mobile` mediumtext DEFAULT NULL,
  `not_clickable` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `direct_link` varchar(255) DEFAULT NULL,
  `controller_dir` varchar(64) DEFAULT NULL,
  `controller_file` varchar(64) DEFAULT NULL,
  `props` text DEFAULT NULL,
  `in_menu` tinyint(1) UNSIGNED DEFAULT NULL,
  `in_map` tinyint(1) UNSIGNED DEFAULT NULL,
  `noindex` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_fixed` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_public` tinyint(1) UNSIGNED DEFAULT 0,
  `is_hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_system` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_parent_ident` (`parent`,`ident`) USING BTREE,
  KEY `IX_is_system` (`is_system`),
  KEY `IX_parent` (`parent`),
  KEY `IX_posit` (`posit`),
  KEY `IX_is_public` (`is_public`),
  KEY `IX_in_menu` (`in_menu`),
  KEY `IX_main_template` (`main_template`),
  KEY `IX_module` (`module`),
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_pages__main_templates` FOREIGN KEY (`main_template`)
    REFERENCES `{$dbTables['main_templates']}` (`ident`) ON UPDATE CASCADE,
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_pages__modules` FOREIGN KEY (`module`)
    REFERENCES `{$dbTables['modules']}` (`ident`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Страницы сайта'",

// Records of "pages"
	"INSERT INTO `{$dbTables['pages']}` (`parent`, `posit`, `name`, `ident`, `full_path`, `h1`, `main_template`, `module`, `content`, `content_mobile`, `not_clickable`, `is_system`, `title`, `description`, `keywords`, `direct_link`, `controller_dir`, `controller_file`, `props`, `is_public`, `in_menu`, `in_map`, `noindex`, `is_hidden`, `is_fixed`) VALUES
  (0, 1, 'Главная', 'front', '/', NULL, 'front', 'html', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 'special', 'front', NULL, 1, 1, 1, NULL, 0, 1),
  (0, 2, 'Trash', 'trash', '/trash/', NULL, 'plain', 'html', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, 1, 1),
  (1, 1, 'Администрирование', '{$adminIdent}', '/{$adminIdent}/', 'Административная панель', 'admin', 'html', NULL, NULL, 0, 1, 'Администрирование', NULL, NULL, NULL, 'admin', 'front', NULL, 0, 0, 0, NULL, 0, 1),
  (3, 1, 'Редактирование объектов', 'entity_edit', '/{$adminIdent}/entity_edit/', NULL, 'admin', 'html', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 'entity_edit', 'form', 'easyui=1', 0, 0, 0, NULL, 0, 1),
  (3, 2, 'Страницы', 'pages', '/{$adminIdent}/pages/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Управление страницами | Администрирование', NULL, NULL, NULL, 'admin', 'pages', 'easyui=1', 0, 1, 0, NULL, 0, 1),
  (3, 3, 'Модули', 'modules', '/{$adminIdent}/modules/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Модули | Администрирование', NULL, NULL, NULL, 'admin', 'modules', NULL, 0, 1, 0, NULL, 0, 1),
  (3, 4, 'Основные настройки', 'site_options_main', '/{$adminIdent}/site_options_main/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Основные настройки | Администрирование', NULL, NULL, NULL, 'admin', 'site_options', 'group=main', 0, 1, 0, NULL, 0, 1),
  (3, 5, 'SEO-настройки', 'site_options_seo', '/{$adminIdent}/site_options_seo/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'SEO-настройки | Администрирование', NULL, NULL, NULL, 'admin', 'site_options', 'group=seo', 0, 1, 0, NULL, 0, 1),
  (3, 6, 'Настройки шаблона', 'site_options_template', '/{$adminIdent}/site_options_template/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Настройки шаблона | Администрирование', NULL, NULL, NULL, 'admin', 'site_options', 'group=template', 0, 1, 0, NULL, 0, 1),
  (3, 7, 'Текстовые блоки', 'text_blocks', '/{$adminIdent}/text_blocks/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Настройки шаблона | Администрирование', NULL, NULL, NULL, 'admin', 'text_blocks', NULL, 0, 1, 0, NULL, 0, 1),
  (3, 8, 'Мини баннеры', 'mini_banners', '/{$adminIdent}/mini_banners/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Настройки шаблона | Администрирование', NULL, NULL, NULL, 'admin', 'mini_banners', NULL, 0, 1, 0, NULL, 0, 1),
  (3, 9, 'Файловый менеджер', 'media_manager', '/{$adminIdent}/media_manager/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Медиа-менеджер | Администрирование', NULL, NULL, NULL, 'admin', 'file_manager', NULL, 0, 1, 0, NULL, 0, 1),
  (3, 10, 'Пользователи', 'users', '/{$adminIdent}/users/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Управление пользователями | Администрирование', NULL, NULL, NULL, 'admin', 'users', 'easyui=1', 0, 1, 0, NULL, 0, 1),
  (3, 11, 'Безопасность', 'guard', '/{$adminIdent}/guard/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Безопасность | Администрирование', NULL, NULL, NULL, 'admin', 'guard', 'easyui=1', 0, 1, 0, NULL, 0, 1),
  (3, 12, 'Информация о сайте', 'site_info', '/{$adminIdent}/site_info/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Информация о сайте | Администрирование', NULL, NULL, NULL, 'admin', 'site_info', NULL, 0, 1, 0, NULL, 0, 1),
  (3, 13, 'Словари', 'dictionaries', '/{$adminIdent}/dictionaries/', NULL, 'admin', 'html', NULL, NULL, 0, 1, 'Управление пользователями | Администрирование', NULL, NULL, NULL, 'admin', 'dictionaries', 'easyui=1&dics=1', 0, 0, 0, NULL, 0, 1),
  (1, 2, 'История', 'history', '/history/', NULL, 'plain', 'html', NULL, NULL, 0, 1, 'Управление пользователями | Администрирование', NULL, NULL, NULL, 'special', 'history', 'easyui=1', 0, 0, 0, NULL, 0, 1),
  (1, 3, 'Страница не найдена', 'page_not_found', '/page_not_found/', NULL, 'standard', 'html', '<p><span style=\"font-size:28px\">404</span>, запрашиваемая страница не была найдена</p>', NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 1),
  (1, 4, 'В доступе отказано', 'access_denied', '/access_denied/', NULL, 'standard', 'html', '<p><span style=\"font-size:28px\">403</span>, доступ к данной странице ограничен</p>', NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 1),
  (1, 5, 'Пользовательское соглашение', 'eula', '/eula/', NULL, 'standard', 'html', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 1),
  (1, 6, 'Политика конфиденциальности', 'privacy_policy', '/privacy_policy/', NULL, 'standard', 'html', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 1),
  (1, 7, 'Авторизация пользователя', 'login-user', '/login-user/', NULL, 'standard', 'html', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 'account', 'login-user', NULL, 1, 0, 0, NULL, 0, 1),
  (1, 8, 'Авторизация AEngine', 'login-admin', '/login-admin/', NULL, 'admin', 'html', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 'account', 'login-admin', NULL, 1, 0, 0, NULL, 0, 1),
  (1, 9, 'Аккаунт', 'account', '/account/', NULL, 'standard', 'html', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 'account', 'account', NULL, 1, 0, 0, NULL, 0, 1),
  (24, 1, 'Регистрация', 'signup', '/account/signup/', NULL, 'standard', 'html', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 'account', 'signup', NULL, 1, 0, 0, NULL, 0, 1),
  (24, 2, 'Управление аккаунтом', 'edit', '/account/edit/', NULL, 'standard', 'html', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 'account', 'edit', NULL, 1, 0, 0, NULL, 0, 1),
  (24, 3, 'Сброс пароля', 'lost-password', '/account/lost-password/', NULL, 'standard', 'html', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 'account', 'password_reset', NULL, 1, 0, 0, NULL, 0, 1),
  (1, 10, 'О нас', 'about-us', '/about-us/', NULL, 'standard', 'html', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 0, 0),
  (1, 11, 'Контакты', 'kontakty', '/kontakty/', NULL, '100vw', 'html', '<p><b>Будем рады сотрудничеству!</b></p><table><tbody><tr><td>Адрес:</td><td>г. Барнаул</td></tr><tr><td>Телефон:</td><td>+7-000-000-00-00</td></tr><tr><td>E-mail:</td><td>example@site.domain</td></tr><tr><td>График работы:</td><td>10.00 - 19.00 Пн-Пт<br/>12.00 - 13.00 Обед<br/>Суббота, воскресенье - выходной</td></tr></tbody></table>', NULL, 0, 0, NULL, NULL, NULL, NULL, 'special', 'contacts', NULL, 1, 1, 1, NULL, 0, 0)",

// Table structure for "page_permissions"
"CREATE TABLE `{$dbTables['page_permissions']}` (
  `page_id` int(10) UNSIGNED NOT NULL,
  `group_id` tinyint(3) UNSIGNED NOT NULL,
  `statuses` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`page_id`,`group_id`),
  KEY `IX_page_id` (`page_id`) USING BTREE,
  KEY `IX_group_id` (`group_id`) USING BTREE,
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_page_permissions__groups` FOREIGN KEY (`group_id`)
    REFERENCES `{$dbTables['groups']}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_page_permissions__pages` FOREIGN KEY (`page_id`)
    REFERENCES `{$dbTables['pages']}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Настройки прав для различных групп пользователей'",

// Records of "page_permissions"
"INSERT INTO `{$dbTables['page_permissions']}` (`page_id`, `group_id`, `statuses`) VALUES
  ('3', '2', '2'),
  ('5', '2', '2'),
  ('6', '2', '2'),
  ('7', '2', '2'),
  ('8', '2', '2'),
  ('9', '2', '2'),
  ('10', '2', '2'),
  ('11', '2', '2'),
  ('12', '2', '2'),
  ('13', '2', '2'),
  ('14', '2', '2'),
  ('15', '2', '2'),
  ('16', '2', '2')",

// Table structure for "registered_triggers"
"CREATE TABLE `{$dbTables['registered_triggers']}` (
  `module` varchar(64) NOT NULL,
  `class` varchar(64) NOT NULL,
  `function` varchar(64) NOT NULL,
  PRIMARY KEY (`module`,`function`),
  CONSTRAINT `FK_registered_triggers__modules` FOREIGN KEY (`module`) REFERENCES `{$dbTables['modules']}` (`ident`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Триггеры модулей'",

// Table structure for "site_options"
"CREATE TABLE `{$dbTables['site_options']}` (
  `ident` varchar(64) NOT NULL,
  `group` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `edit_mode` varchar(16) NOT NULL,
  `is_required` tinyint(1) UNSIGNED DEFAULT 0,
  `posit` smallint(2) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`ident`),
  UNIQUE KEY `IX_ident` (`ident`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Настройки сайта'",

// Records of "site_options"
"INSERT INTO `{$dbTables['site_options']}` (`ident`, `group`, `value`, `name`, `description`, `edit_mode`, `is_required`, `posit`) VALUES
  ('site_name', '" . SiteOptions::MAIN . "', 'CMF AEngine', 'Название сайта', 'Например, название компании. Отображается в автоматически отправляемых сообщениях, и не только.', 'text', 1, 1),
  ('admin_email', '" . SiteOptions::MAIN . "', '{$adminEmail}', 'E-mail администратора', 'Этот адрес используется в целях администрирования. По-умолчанию на этот адрес приходят все уведомления.', 'text', 1, 2),
  ('robot_email', '" . SiteOptions::MAIN . "', 'noreply@web-ae.ru', 'Адрес отправителя сообщений', 'Этот адрес указывается в качестве отправителя автоматических сообщений, если <strong>ОТКЛЮЧЕНА</strong> отправка с помощью <strong>SMTP-сервера</strong>.', 'text', 0, 3),
  ('cont_phone', '" . SiteOptions::MAIN . "', '', 'Телефон', 'Контактный телефон, в мобильной версии отобразится в виде зафиксированной внизу кнопки.', 'text', 0, 5),
  ('delete_with_subpages', '" . SiteOptions::MAIN . "', '0', 'Удалять страницы рекурсивно', 'При удалении страницы все дочерние страницы также будут удалены. При выключенной опции дочерние страницы отправляются в <strong>Корзину</strong> с возможностью их восстановления.', 'checkbox', 0, 6),
  ('items_per_page', '" . SiteOptions::MAIN . "', '15', 'Количество записей на одной странице', 'Для постраничного вывода.', 'text', 1, 7),
  ('copyright', '" . SiteOptions::MAIN . "', '(c) Все права защищены', 'Копирайт', 'Добавляется в автоматически отправляемые сообщения. Так же может быть размещён в подвале сайта.', 'text', 0, 8),
  ('service_mode_is_on', '" . SiteOptions::MAIN . "', '0', 'Включить сервисный режим', 'Сайт блокируется для всех кроме Администратора, остальные пользователи будут видеть специальное сообщение.', 'checkbox', 0, 9),
  ('service_mode_message', '" . SiteOptions::MAIN . "', '<p>На сайте проводятся технические работы.</p><p>К сожалению, доступ к сайту временно отключен.</p>', 'Информер сервисного режима', 'Текстовое сообщение, которое отображается вместо контента страницы. Это может быть сообщение, о том, что сайт временно недоступен, можно указать контакты для связи.', 'htmleditor', 1, 10),
  ('allow_registration', '" . SiteOptions::MAIN . "', '0', 'Включить регистрацию пользовалей', 'Открывается доступ к странице регистрации. Любой пользователь может создать свой аккаунт. При выключении регистрации также запрещается авторизация всех пользователей кроме администратора.', 'checkbox', 0, 11),

  ('main_banner', '" . SiteOptions::TPL . "', '', 'Баннер на главной странице', 'Изображение баннера. Размеры произвольные, но ширина не должна превышать 4000px. Если ширина баннера меньше ширины экрана, то баннер отобразится по центру.', 'url', 0, 1),
  ('form_on_banner', '" . SiteOptions::TPL . "', '0', 'Добавить форму на банер', 'Форма обратной связи будет отображаться поверх главного банера с левой стороны.', 'checkbox', 0, 3),
  ('theme_color_1', '" . SiteOptions::TPL . "', '#80C2DC', 'Код цвета #1', 'Пример: #000000', 'color', 1, 4),
  ('theme_color_2', '" . SiteOptions::TPL . "', '#248BB8', 'Код цвета #2', 'Пример: #000000', 'color', 1, 5),

  ('user_html', '" . SiteOptions::SEO . "', '', 'Пользовательский HTML-код', 'Код счётчиков и т.д. Вы должны точно понимать, что это такое!', 'textarea', 0, 1),
  ('counter_id', '" . SiteOptions::SEO . "', '', 'id счётчика Яндекс-метрики', 'id счётчика Яндекс-метрики, к которому будут привязываться цели', 'text', 0, 2),
  ('metadata', '" . SiteOptions::SEO . "', '', 'Дополнительные мета-теги', 'Код, добавляемый внутрь тега HEAD. Вы должны точно понимать, что это такое!', 'textarea', 0, 3),
  ('site_description', '" . SiteOptions::SEO . "', '', 'Описание сайта', 'Объясните в нескольких словах, о чём этот сайт. Используется, если для конкретной страницы не указано описание.', 'textarea', 0, 4),
  ('site_keywords', '" . SiteOptions::SEO . "', '', 'Ключевые слова', 'Используется, если для конкретной страницы не указаны ключевые слова.', 'text', 0, 5),
  ('robots_extra', '" . SiteOptions::SEO . "', '', 'Дополнение к robots.txt', 'Вы должны точно понимать, что это такое!', 'textarea', 0, 6),
  ('sitemap_enabled', '" . SiteOptions::SEO . "', '1', 'Включить sitemap.xml', 'Файлы карты сайта будут автоматически обновляться при изменении входящих в неё страниц.', 'checkbox', 0, 7),
  ('insert_specific_pages_in_sitemap', '" . SiteOptions::SEO . "', '0', 'Дополнительные страницы в sitemap.xml', 'Добавлять в карту сайта специфичные страницы (новости, товары, категории товаров и т.п.) если таковые имеются.', 'checkbox', 0, 8)",

// Table structure for "text_blocks"
"CREATE TABLE `{$dbTables['text_blocks']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ident` varchar(128) NOT NULL,
  `description` varchar(128),
  `content` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_ident` (`ident`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Текстовые блоки'",

// Records of "text_blocks"
"INSERT INTO `{$dbTables['text_blocks']}` (`ident`, `description`, `content`) VALUES
  ('HTML_BLOCK_ADDRESS', 'Адрес в шапке и подвале сайта', NULL),
  ('HTML_BLOCK_CONTACT_MAP', 'Код виджета-карты на странице Контакты', NULL),
  ('HTML_BLOCK_FOOTER_TITLE', 'Заголовок или логотип в подвале сайта', NULL),
  ('HTML_BLOCK_HEADER_TITLE', 'Заголовок или логотип в шапке сайта', NULL),
  ('HTML_BLOCK_PHONES', 'Телефон в шапке и подвале сайта', NULL)",

// Table structure for "users"
"CREATE TABLE `{$dbTables['users']}` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `statuses` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_login` (`login`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Пользователи'",

// Records of "users"
"INSERT INTO `{$dbTables['users']}` (`login`, `password`, `email`, `name`, `description`,  `statuses`) VALUES
  ('guest', NULL, '', 'Гость', 'Гостевая учётная запись', 1),
  ('{$adminLogin}', '{$adminPassHash}', '{$adminEmail}', 'Администратор', 'Системная запись', 2)",

// Table structure for "user_groups"
"CREATE TABLE `{$dbTables['user_groups']}` (
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `group_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `IX_user_id` (`user_id`),
  KEY `IX_group_id` (`group_id`),
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_user_groups__users` FOREIGN KEY (`user_id`)
    REFERENCES `{$dbTables['users']}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" . Cfg::DB_TBL_PREFIX . "FK_user_groups__groups` FOREIGN KEY (`group_id`)
    REFERENCES `{$dbTables['groups']}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Связь пользователей с группами'",

// Records of "user_groups"
"INSERT INTO `{$dbTables['user_groups']}` (`user_id`, `group_id`) VALUES
  (1, 1),
  (2, 2),
  (2, 3),
  (2, 4)"
];

foreach ($queries as $sql) {
	DBCommand::query($sql);
}

DBCommand::enableConstraints();

echo 'БД наполнена начальными данными.', PHP_EOL,
	"Учётная запись администратора: {$adminLogin} @ {$adminPass}", PHP_EOL;
