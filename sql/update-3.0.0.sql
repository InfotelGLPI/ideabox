CREATE TABLE `glpi_plugin_ideabox_configs` (
    `id` int unsigned NOT NULL auto_increment,
    `title` varchar(255) collate utf8mb4_unicode_ci default '',
    `comment` varchar(255) collate utf8mb4_unicode_ci default '',
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_ideabox_configs` VALUES(1, '','');
