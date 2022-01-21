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

include('../../../inc/includes.php');

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";
if (!isset($_GET["plugin_ideabox_ideaboxes_id"])) $_GET["plugin_ideabox_ideaboxes_id"] = "";

$comment = new PluginIdeaboxComment();

if (isset($_POST["add"])) {

   $comment->check(-1, CREATE, $_POST);
   $comment->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {

   $comment->check($_POST['id'], UPDATE);
   $comment->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {

   $comment->check($_POST['id'], DELETE);
   $comment->delete($_POST, 1);
   Html::redirect(Toolbox::getItemTypeFormURL('PluginIdeaboxIdeabox') . "?id=" . $_POST["plugin_ideabox_ideaboxes_id"]);

} else if (isset($_POST["delete_comment"])) {
   foreach ($_POST["check"] as $ID => $value) {
      $comment->check($ID, DELETE);
      $comment->delete(["id" => $ID], 1);
   }
   Html::back();

} else {
   $plugin = new Plugin();
   $comment->checkGlobal(READ);

   if (Session::getCurrentInterface() == 'central') {
      Html::header(PluginIdeaboxIdeabox::getTypeName(2), '', "tools", "pluginideaboxideabox");
   } else {
      if ($plugin->isActivated('servicecatalog')) {
         PluginServicecatalogMain::showDefaultHeaderHelpdesk(PluginIdeaboxIdeabox::getTypeName(2), true);
      } else {
         Html::helpHeader(PluginIdeaboxIdeabox::getTypeName(2));
      }
   }

   $comment->showForm($_GET["id"], ['plugin_ideabox_ideaboxes_id' => $_GET["plugin_ideabox_ideaboxes_id"]]);

   if (Session::getCurrentInterface() != 'central'
       && $plugin->isActivated('servicecatalog')) {

      PluginServicecatalogMain::showNavBarFooter('ideabox');
   }

   if (Session::getCurrentInterface() == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }
}

?>
