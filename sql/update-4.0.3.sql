--
-- -------------------------------------------------------------------------
-- ideabox plugin for GLPI
-- Copyright (C) 2025-2026 by the ideabox Development Team.
--
-- https://github.com/InfotelGLPI/ideabox
-- -------------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of ideabox.
--
-- ideabox is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- ideabox is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with ideabox. If not, see <http://www.gnu.org/licenses/>.
-- --------------------------------------------------------------------------
--

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

UPDATE `glpi_items_tickets` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_items_problems` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
UPDATE `glpi_documents_items` SET `itemtype` = 'GlpiPlugin\\Ideabox\\Ideabox' WHERE `itemtype` = 'PluginIdeaboxIdeabox';
