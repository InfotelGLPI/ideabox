DROP TABLE IF EXISTS `glpi_plugin_ideabox_ideaboxs`;
CREATE TABLE `glpi_plugin_ideabox_ideaboxs` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	`users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`date_idea` DATETIME default NULL,
	`is_deleted` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `users_id` (`users_id`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_ideabox_comments`;
CREATE TABLE `glpi_plugin_ideabox_comments` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`date_comment` DATETIME default NULL,
	`plugin_ideabox_ideaboxs_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_ideabox (id)',
	`users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `plugin_ideabox_ideaboxs_id` (`plugin_ideabox_ideaboxs_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_ideabox_profiles`;
CREATE TABLE `glpi_plugin_ideabox_profiles` (
	`id` int(11) NOT NULL auto_increment,
	`profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	`ideabox` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Idea', 'PluginIdeaboxIdeabox', '2010-02-17 22:36:46','');

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'4900','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'4900','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'4900','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'4901','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'4901','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'4901','4','3','0');