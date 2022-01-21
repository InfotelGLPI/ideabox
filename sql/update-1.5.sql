ALTER TABLE `glpi_plugin_ideabox` ADD `begin_date` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `author`;
INSERT INTO `glpi_displaypreferences` ( `ID` , `type` , `num` , `rank` , `FK_users` ) VALUES (NULL,'4901','2','1','0');
INSERT INTO `glpi_displaypreferences` ( `ID` , `type` , `num` , `rank` , `FK_users` ) VALUES (NULL,'4901','3','2','0');
INSERT INTO `glpi_displaypreferences` ( `ID` , `type` , `num` , `rank` , `FK_users` ) VALUES (NULL,'4901','4','3','0');