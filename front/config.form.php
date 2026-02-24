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

use GlpiPlugin\Ideabox\Config;

Session::checkLoginUser();

if (Plugin::isPluginActive("ideabox")) {
    Session::checkRight("config", UPDATE);

    $config = new Config();

    if (isset($_POST["update_setup"])) {
         $_POST['id'] = 1;
         $config->update($_POST);

        Html::back();
    } else {
        Html::header(__s('Setup'), '', "helpdesk", "pluginideabox", "config");
        $_GET['id'] = 1;
        $config->display($_GET);
        Html::footer();
    }
} else {
    Html::header(__s('Setup'), '', "config", "plugins");
    echo "<div class='alert alert-important alert-warning d-flex'>";
    echo "<b>".__s('Please activate the plugin', 'ideabox')."</b></div>";
    Html::footer();
}
