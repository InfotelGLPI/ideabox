DROP TABLE IF EXISTS `glpi_plugin_ideabox_mailing`;
ALTER TABLE `glpi_plugin_ideabox` RENAME `glpi_plugin_ideabox_ideaboxs`;

ALTER TABLE `glpi_plugin_ideabox_ideaboxs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `author` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `begin_date` `date_idea` DATETIME default NULL,
   CHANGE `description` `comment` text collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`users_id`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_ideabox_comments` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `date` `date_comment` DATETIME default NULL,
   CHANGE `FK_ideabox` `plugin_ideabox_ideaboxs_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_ideabox (id)',
   CHANGE `author` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   ADD INDEX (`name`),
   ADD INDEX (`plugin_ideabox_ideaboxs_id`),
   ADD INDEX (`users_id`);

ALTER TABLE `glpi_plugin_ideabox_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `ideabox` `ideabox` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Idea', 'PluginIdeaboxIdeabox', '2010-02-17 22:36:46','',NULL);
   
DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 4901;