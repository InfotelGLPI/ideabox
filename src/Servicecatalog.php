<?php

/*
 -------------------------------------------------------------------------
 ideabox plugin for GLPI
 Copyright (C) 2025-2026 by the ideabox Development Team.

 https://github.com/InfotelGLPI/ideabox
 -------------------------------------------------------------------------

 LICENSE

 This file is part of ideabox.

 ideabox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 ideabox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with ideabox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Ideabox;

use CommonGLPI;
use Session;

class Servicecatalog extends CommonGLPI
{
    public static $rightname = 'plugin_ideabox';

    public $dohistory = false;

    public static function canUse()
    {
        return Session::haveRight(self::$rightname, READ);
    }

    /**
     * @return string
     */
    public static function getMenuLink()
    {

        return PLUGIN_IDEABOX_WEBDIR . "/front/ideabox.php";
    }

    /**
     * @return string
     */
    public static function getNavBarLink()
    {

        return PLUGIN_IDEABOX_WEBDIR . "/front/ideabox.php";
    }

    public static function getMenuLogo()
    {

        return Ideabox::getIcon();
    }

    /**
     * @return string
     * @throws \GlpitestSQLError
     */
    public static function getMenuLogoCss()
    {

        $addstyle = "font-size: 4.5em;";
        return $addstyle;
    }

    public static function getMenuTitle()
    {

        return __s('I have an idea', 'ideabox');
    }


    public static function getMenuComment()
    {

        return __s('I have an idea', 'ideabox');
    }

    public static function getLinkList()
    {
        return "";
    }

    public static function getList()
    {
        return "";
    }
}
