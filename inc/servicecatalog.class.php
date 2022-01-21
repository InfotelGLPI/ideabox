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


class PluginIdeaboxServicecatalog extends CommonGLPI {

   static $rightname = 'plugin_ideabox';

   var $dohistory = false;

   static function canUse() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return string
    */
   static function getMenuLink() {

      return PLUGIN_IDEABOX_DIR . "/front/ideabox.php";
   }

   /**
    * @return string
    */
   static function getNavBarLink() {

      return PLUGIN_IDEABOX_NOTFULL_DIR . "/front/ideabox.php";
   }

   static function getMenuLogo() {

      return PluginIdeaboxIdeabox::getIcon();

   }

   /**
    * @return string
    * @throws \GlpitestSQLError
    */
   static function getMenuLogoCss() {

      $addstyle = "font-size: 4.5em;";
      return $addstyle;

   }

   static function getMenuTitle() {

      return __('I have an idea', 'ideabox');

   }


   static function getMenuComment() {

      return __('I have an idea', 'ideabox');
   }

   static function getLinkList() {
      return "";
   }

   static function getList() {
      return "";
   }
}
