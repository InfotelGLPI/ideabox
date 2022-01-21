<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 ideaxox plugin for GLPI
 Copyright (C) 2022-2023 by the ideaxox Development Team.

 https://github.com/InfotelGLPI/ideaxox
 -------------------------------------------------------------------------

 LICENSE

 This file is part of ideaxox.

 ideaxox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 ideaxox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with ideaxox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_ideabox_install() {
   global $DB;

   include_once(PLUGIN_IDEABOX_DIR . "/inc/profile.class.php");


   $DB->runFile(GLPI_ROOT . "/plugins/ideabox/sql/empty-3.0.0.sql");

   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginIdeaboxIdeabox' AND `name` = 'Idea'";
   $result = $DB->query($query_id) or die($DB->error());
   $itemtype = $DB->result($result, 0, 'id');
   if (empty($itemtype)) {
      $query_id = "INSERT INTO `glpi_notificationtemplates`(`name`, `itemtype`, `date_mod`, `comment`, `css`) VALUES ('Idea','PluginIdeaboxIdeabox', NOW(),'','');";
      $result = $DB->query($query_id) or die($DB->error());
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginIdeaboxIdeabox' AND `name` = 'Idea'";
      $result = $DB->query($query_id) or die($DB->error());
      $itemtype = $DB->result($result, 0, 'id');
   }

   $query = "INSERT INTO `glpi_notificationtemplatetranslations`
                              VALUES(NULL, '" . $itemtype . "', '','##lang.ideabox.title##',
                     '##lang.ideabox.url## : ##ideabox.url##
   ##lang.ideabox.entity## : ##ideabox.entity##
   ##IFideabox.name####lang.ideabox.name## : ##ideabox.name##
   ##ENDIFideabox.name##
   ##IFideabox.comment####lang.ideabox.comment## : ##ideabox.comment##
   ##ENDIFideabox.comment##

   ##FOREACHupdates##----------
   ##lang.update.title##:
   ##IFupdate.name####lang.ideabox.name## : ##update.name####ENDIFupdate.name##
   ##IFupdate.comment##
   ##lang.ideabox.comment## : ##update.comment##
   ##ENDIFupdate.comment##
   ----------##ENDFOREACHupdates##

   ##lang.comment.title##
   ----------
   ##FOREACHcomments##
   ##IFcomment.name####lang.comment.name## : ##comment.name####ENDIFcomment.name##
   ##IFcomment.author####lang.comment.author## : ##comment.author####ENDIFcomment.author##
   ##IFcomment.datecomment####lang.comment.datecomment## : ##comment.datecomment####ENDIFcomment.datecomment##
   ##IFcomment.comment####lang.comment.comment## : ##comment.comment####ENDIFcomment.comment##
   -------
   ##ENDFOREACHcomments##',
                     '&lt;p&gt;&lt;strong&gt;##lang.ideabox.url##&lt;/strong&gt; : &lt;a href=\"##ideabox.url##\"&gt;##ideabox.url##&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;##lang.ideabox.entity##&lt;/strong&gt; : ##ideabox.entity##&lt;br /&gt; ##IFideabox.name##&lt;strong&gt;##lang.ideabox.name##&lt;/strong&gt; : ##ideabox.name####ENDIFideabox.name##&lt;br /&gt;&lt;br /&gt; ##IFideabox.comment##&lt;strong&gt;##lang.ideabox.comment##&lt;/strong&gt; : ##ideabox.comment####ENDIFideabox.comment##&lt;br /&gt;&lt;br /&gt;##FOREACHupdates##----------&lt;br /&gt;&lt;strong&gt;##lang.update.title## :&lt;/strong&gt;&lt;br /&gt;##IFupdate.name##&lt;strong&gt;##lang.ideabox.name##&lt;/strong&gt; : ##update.name####ENDIFupdate.name##&lt;br /&gt;##IFupdate.comment##&lt;br /&gt;&lt;strong&gt;##lang.ideabox.comment##&lt;/strong&gt; : ##update.comment##&lt;br /&gt;##ENDIFupdate.comment##&lt;br /&gt;----------##ENDFOREACHupdates##&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;##lang.comment.title## :&lt;/strong&gt;&lt;br /&gt;----------&lt;br /&gt;##FOREACHcomments####IFcomment.name##&lt;strong&gt;##lang.comment.name##&lt;/strong&gt; : ##comment.name####ENDIFcomment.name##&lt;br /&gt;##IFcomment.author##&lt;strong&gt;##lang.comment.author##&lt;/strong&gt; : ##comment.author####ENDIFcomment.author##&lt;br /&gt;##IFcomment.datecomment##&lt;strong&gt;##lang.comment.datecomment##&lt;/strong&gt; : ##comment.datecomment####ENDIFcomment.datecomment##&lt;br /&gt;##IFcomment.comment##&lt;strong&gt;##lang.comment.comment##&lt;/strong&gt; : ##comment.comment####ENDIFcomment.comment##&lt;br /&gt;----------&lt;br /&gt;##ENDFOREACHcomments##&lt;/p&gt;');";

   $DB->query($query);

   $query = "INSERT INTO `glpi_notifications` (name, entities_id, itemtype, event, is_recursive, is_active)
                VALUES ('New Idea', 0, 'PluginIdeaboxIdeabox', 'new', 1, 1);";
   $DB->query($query);
   $query = "INSERT INTO `glpi_notifications` (name, entities_id, itemtype, event, is_recursive, is_active)
                VALUES ('Update Idea', 0, 'PluginIdeaboxIdeabox', 'update', 1, 1);";
   $DB->query($query);
   $query = "INSERT INTO `glpi_notifications` (name, entities_id, itemtype, event, is_recursive, is_active)
                VALUES ('Delete Idea', 0, 'PluginIdeaboxIdeabox', 'delete', 1, 1);";
   $DB->query($query);
   $query = "INSERT INTO `glpi_notifications` (name, entities_id, itemtype, event, is_recursive, is_active)
                VALUES ('New comment of idea', 0, 'PluginIdeaboxIdeabox', 'newcomment', 1, 1);";
   $DB->query($query);
   $query = "INSERT INTO `glpi_notifications` (name, entities_id, itemtype, event, is_recursive, is_active)
                VALUES ('Update comment of idea', 0, 'PluginIdeaboxIdeabox', 'updatecomment', 1, 1);";
   $DB->query($query);
   $query = "INSERT INTO `glpi_notifications` (name, entities_id, itemtype, event, is_recursive, is_active)
                VALUES ('Delete comment of idea', 0, 'PluginIdeaboxIdeabox', 'deletecomment', 1, 1);";
   $DB->query($query);

   PluginIdeaboxProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_ideabox_uninstall() {
   global $DB;

   $tables = ["glpi_plugin_ideabox_ideaboxs",
              "glpi_plugin_ideabox_ideaboxes",
              "glpi_plugin_ideabox_comments"];

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   $notif = new Notification();

   $options = ['itemtype' => 'PluginIdeaboxIdeabox',
               'event'    => 'new',
               'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = ['itemtype' => 'PluginIdeaboxIdeabox',
               'event'    => 'update',
               'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = ['itemtype' => 'PluginIdeaboxIdeabox',
               'event'    => 'delete',
               'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   $options = ['itemtype' => 'PluginIdeaboxIdeabox',
               'event'    => 'newcomment',
               'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = ['itemtype' => 'PluginIdeaboxIdeabox',
               'event'    => 'updatecomment',
               'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = ['itemtype' => 'PluginIdeaboxIdeabox',
               'event'    => 'deletecomment',
               'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   //templates
   $template       = new NotificationTemplate();
   $translation    = new NotificationTemplateTranslation();
   $notif_template = new Notification_NotificationTemplate();
   $options        = ['itemtype' => 'PluginIdeaboxIdeabox',
                      'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = ['notificationtemplates_id' => $data['id'],
                           'FIELDS'                   => 'id'];

      foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
         $translation->delete($data_template);
      }
      $template->delete($data);

      foreach ($DB->request('glpi_notifications_notificationtemplates', $options_template) as $data_template) {
         $notif_template->delete($data_template);
      }
   }

   $tables_glpi = $tables_glpi = ["glpi_displaypreferences",
                                  "glpi_documents_items",
                                  "glpi_savedsearches",
                                  "glpi_logs",
                                  "glpi_items_tickets",
                                  "glpi_notepads",
                                  "glpi_dropdowntranslations"];

   foreach ($tables_glpi as $table_glpi)
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginIdeaboxIdeabox' OR `itemtype` = 'PluginIdeaboxComment';");

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(['itemtype' => 'PluginIdeaboxIdeabox']);
   }

   return true;
}

function plugin_ideabox_AssignToTicket($types) {

   if (Session::haveRight("plugin_ideabox_open_ticket", "1")) {
      $types['PluginIdeaboxIdeabox'] = PluginIdeaboxIdeabox::getTypeName(2);
   }

   return $types;
}

// Define dropdown relations
function plugin_ideabox_getDatabaseRelations() {
   $plugin = new Plugin();
   if ($plugin->isActivated("ideabox"))
      return ["glpi_entities"                 => ["glpi_plugin_ideabox_ideaboxes" => "entities_id"],
              "glpi_users"                    => ["glpi_plugin_ideabox_ideaboxes" => "users_id",
                                                  "glpi_plugin_ideabox_comments"  => "users_id"],
              "glpi_plugin_ideabox_ideaboxes" => ["glpi_plugin_ideabox_comments" => "plugin_ideabox_ideaboxes_id"]];
   else
      return [];
}


function plugin_datainjection_populate_ideabox() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginIdeaboxIdeaboxInjection'] = 'ideabox';
}

?>
