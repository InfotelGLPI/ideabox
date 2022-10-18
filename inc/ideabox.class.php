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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

use Glpi\Application\View\TemplateRenderer;

class PluginIdeaboxIdeabox extends CommonDBTM {

   public    $dohistory  = true;
   static    $rightname  = "plugin_ideabox";
   protected $usenotepad = true;

   static function getTypeName($nb = 0) {

      return _n('Idea', 'Ideas', $nb, 'ideabox');
   }

   /**
    * @return string
    */
   static function getIcon() {
      return "ti ti-bulb";
   }

   //clean if ideabox are deleted
   function cleanDBonPurge() {

      $temp = new PluginIdeaboxComment();
      $temp->deleteByCriteria(['plugin_ideabox_ideaboxes_id' => $this->fields['id']]);
   }

   /**
    * @return array
    */
//   static function getMenuContent() {
//
//      $menu                    = [];
//      $menu['title']           = self::getMenuName();
//      $menu['page']            = self::getSearchURL(false);
//      $menu['links']['search'] = self::getSearchURL(false);
//      if (self::canCreate()) {
//         $menu['links']['add'] = self::getFormURL(false);
//      }
//
//      $menu['icon']    = self::getIcon();
//
//      return $menu;
//   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginIdeaboxIdeabox'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginIdeaboxIdeabox']);
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['pluginideaboxideabox'])) {
         unset($_SESSION['glpimenu']['tools']['content']['pluginideaboxideabox']);
      }
   }

   /**
    * @param array $options
    *
    * @return array
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginIdeaboxComment', $ong, $options);
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $this->addStandardTab('Ticket', $ong, $options);
         $this->addStandardTab('Item_Problem', $ong, $options);
         $this->addStandardTab('Document_Item', $ong, $options);
         $this->addStandardTab('Note', $ong, $options);
         $this->addStandardTab('Log', $ong, $options);
      }

      return $ong;
   }

    /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType(),
      ];

      $tab[] = [
         'id'            => '7',
         'table'         => $this->getTable(),
         'field'         => 'date_idea',
         'name'          => __('Date of submission', 'ideabox'),
         'datatype'      => 'datetime',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'       => '10',
         'table'    => 'glpi_users',
         'field'    => 'name',
         'name'     => __('Author'),
         'datatype' => 'dropdown',
         'right'    => 'all',
      ];

      $tab[] = [
         'id'       => '8',
         'table'    => $this->getTable(),
         'field'    => 'comment',
         'name'     => __('Description', 'ideabox'),
         'datatype' => 'text',
      ];

      $tab[] = [
         'id'       => '9',
         'table'    => $this->getTable(),
         'field'    => 'is_helpdesk_visible',
         'name'     => __('Associable to a ticket'),
         'datatype' => 'bool',
      ];

      $tab[] = [
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number',
      ];

      $tab[] = [
         'id'       => '80',
         'table'    => 'glpi_entities',
         'field'    => 'completename',
         'name'     => __('Entity'),
         'datatype' => 'dropdown',
      ];

      return $tab;
   }


   function post_addItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"]) {
         NotificationEvent::raiseEvent("new", $this);
      }
   }

   function prepareInputForAdd($input) {

      $input['users_id'] = Session::getLoginUserID();
      $input['date_idea'] = $_SESSION["glpi_currenttime"];

      return $input;
   }

   function prepareInputForUpdate($input) {

      if (isset($input['users_id'])) unset($input['users_id']);

      return $input;
   }

   function post_updateItem($history = 1) {
      global $CFG_GLPI;

      if (count($this->updates)) {
         if ($CFG_GLPI["use_mailing"]) {
            NotificationEvent::raiseEvent('update', $this);
         }
      }
   }

   function pre_deleteItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"]) {
         NotificationEvent::raiseEvent("delete", $this);
      }

      return true;
   }

   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);

      if ($ID < 1){
         $options['users_id'] = Session::getLoginUserID();
         $options['date_idea'] = $_SESSION["glpi_currenttime"];
      } else {
         $options['users_id'] = $this->fields['users_id'];
         $options['date_idea'] = $this->fields['date_idea'];
      }

      TemplateRenderer::getInstance()->display('@ideabox/ideabox_form.html.twig', [
         'item'   => $this,
         'params' => $options,
      ]);

      return true;
   }
}

?>
