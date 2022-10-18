CREATE TABLE `glpi_plugin_ideabox_configs` (
    `id` int unsigned NOT NULL auto_increment,
    `title` varchar(255) collate utf8mb4_unicode_ci default '',
    `comment` varchar(255) collate utf8mb4_unicode_ci default '',
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_ideabox_configs` VALUES(1, '','');

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
