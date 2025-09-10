UPDATE `glpi_displaypreferences` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `glpi_displaypreferences`.`itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_displaypreferences` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Comment' WHERE `glpi_displaypreferences`.`itemtype` = 'PluginIdeaboxComment';
UPDATE `glpi_notificationtemplates` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_notifications` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_impactrelations` SET `itemtype_source` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype_source` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_impactrelations` SET `itemtype_impacted` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype_impacted` = 'PluginIdeaboxIdeabox';

UPDATE `glpi_documents_items` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_savedsearches` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_items_tickets` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_dropdowntranslations` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_savedsearches_users` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_notepads` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
