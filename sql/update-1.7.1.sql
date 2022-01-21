RENAME TABLE `glpi_plugin_ideabox_ideaboxs` TO `glpi_plugin_ideabox_ideaboxes` ;

ALTER TABLE `glpi_plugin_ideabox_ideaboxes` 
   ADD `is_helpdesk_visible` int(11) NOT NULL default '1',
   ADD INDEX (`is_helpdesk_visible`);

ALTER TABLE `glpi_plugin_ideabox_comments` 
   CHANGE `plugin_ideabox_ideaboxs_id` `plugin_ideabox_ideaboxes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_ideabox_ideaboxes (id)';
   
ALTER TABLE `glpi_plugin_ideabox_profiles` 
   ADD `open_ticket` char(1) collate utf8_unicode_ci default NULL;
