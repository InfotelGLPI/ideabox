ALTER TABLE `glpi_plugin_ideabox` CHANGE `begin_date` `begin_date` DATETIME NULL default NULL;
UPDATE `glpi_plugin_ideabox` SET `begin_date` = NULL WHERE `begin_date` ='0000-00-00 00:00:00';
ALTER TABLE `glpi_plugin_ideabox_comments` CHANGE `date` `date` DATETIME NULL default NULL;
UPDATE `glpi_plugin_ideabox_comments` SET `date` = NULL WHERE `date` ='0000-00-00 00:00:00';

ALTER TABLE `glpi_plugin_ideabox_profiles` DROP COLUMN `interface` , DROP COLUMN `is_default`;