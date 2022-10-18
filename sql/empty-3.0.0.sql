DROP TABLE IF EXISTS `glpi_plugin_ideabox_ideaboxes`;
CREATE TABLE `glpi_plugin_ideabox_ideaboxes` (
    `id` int unsigned NOT NULL auto_increment,
    `entities_id` int unsigned NOT NULL default '0',
    `is_recursive` tinyint NOT NULL default '0',
    `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
    `comment` text collate utf8mb4_unicode_ci,
    `users_id` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
    `date_idea` timestamp default NULL,
    `is_helpdesk_visible` int unsigned NOT NULL default '1',
    `is_deleted` tinyint(1) NOT NULL default '0',
    PRIMARY KEY  (`id`),
    KEY `name` (`name`),
    KEY `entities_id` (`entities_id`),
    KEY `users_id` (`users_id`),
    KEY `is_deleted` (`is_deleted`),
    KEY `is_helpdesk_visible` (`is_helpdesk_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_ideabox_comments`;
CREATE TABLE `glpi_plugin_ideabox_comments` (
    `id` int unsigned NOT NULL auto_increment,
    `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
    `date_comment` timestamp default NULL,
    `plugin_ideabox_ideaboxes_id` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_ideabox_ideaboxes (id)',
    `users_id` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
    `comment` text collate utf8mb4_unicode_ci,
    PRIMARY KEY  (`id`),
    KEY `name` (`name`),
    KEY `plugin_ideabox_ideaboxes_id` (`plugin_ideabox_ideaboxes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


DROP TABLE IF EXISTS `glpi_plugin_ideabox_configs`;
CREATE TABLE `glpi_plugin_ideabox_configs` (
    `id` int unsigned NOT NULL auto_increment,
    `title` varchar(255) collate utf8mb4_unicode_ci default '',
    `comment` varchar(255) collate utf8mb4_unicode_ci default '',
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_ideabox_configs` VALUES(1, '','');

DROP TABLE IF EXISTS `glpi_plugin_ideabox_configtranslations`;
CREATE TABLE `glpi_plugin_ideabox_configtranslations`
(
    `id`       int unsigned NOT NULL AUTO_INCREMENT,
    `items_id` int unsigned NOT NULL DEFAULT '0',
    `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `language` varchar(5) COLLATE utf8mb4_unicode_ci   DEFAULT NULL,
    `field`    varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `value`    text COLLATE utf8mb4_unicode_ci         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginIdeaboxIdeabox','1','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginIdeaboxIdeabox','7','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginIdeaboxIdeabox','10','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginIdeaboxComment','1','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginIdeaboxComment','7','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginIdeaboxComment','10','3','0');
