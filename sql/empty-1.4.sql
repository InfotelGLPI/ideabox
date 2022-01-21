DROP TABLE IF EXISTS `glpi_plugin_ideabox_profiles`;
CREATE TABLE `glpi_plugin_ideabox_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) default NULL,
	`interface` varchar(50) NOT NULL default 'ideabox',
	`is_default` enum('0','1') NOT NULL default '0',
	`ideabox` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `glpi_plugin_ideabox`;
CREATE TABLE `glpi_plugin_ideabox` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(50),
	`description` LONGTEXT,
	`author` int(11) NOT NULL default '0',
	`deleted` smallint(6) NOT NULL default '0'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_ideabox_mailing`;
CREATE TABLE `glpi_plugin_ideabox_mailing` (
  `ID` int(11) NOT NULL auto_increment,
  `type` varchar(255) collate utf8_unicode_ci default NULL,
  `FK_item` int(11) NOT NULL default '0',
  `item_type` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `mailings` (`type`,`FK_item`,`item_type`),
  KEY `type` (`type`),
  KEY `FK_item` (`FK_item`),
  KEY `item_type` (`item_type`),
  KEY `items` (`item_type`,`FK_item`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_ideabox_comments`;
CREATE TABLE `glpi_plugin_ideabox_comments` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`date` datetime NOT NULL default '0000-00-00',
	`FK_ideabox` int(11) NOT NULL default '0',
	`author` int(11) NOT NULL default '0',
	`comments` text,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO glpi_plugin_ideabox_mailing VALUES ('1','ideabox','1','1');

INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4900','2','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4900','3','2','0');