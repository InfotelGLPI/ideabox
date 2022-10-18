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

if (Session::getCurrentInterface() == 'central') {
    Html::header(PluginIdeaboxIdeabox::getTypeName(2), '', "tools", PluginIdeaboxIdeabox::getType());
} else {
    if (Plugin::isPluginActive('servicecatalog')) {
        PluginServicecatalogMain::showDefaultHeaderHelpdesk(PluginIdeaboxIdeabox::getTypeName(2));
    } else {
        Html::helpHeader(PluginIdeaboxIdeabox::getTypeName(2));
    }
}

$idea = new PluginIdeaboxIdeabox();
if ($idea->canView() || Session::haveRight("config", UPDATE)) {
    if ($_SESSION['glpiactiveprofile']['interface'] != 'central') {
        if ($idea->canCreate()) {
            echo "<div class='center'><table class='tab_cadre_fixe' cellpadding='5'>";
            $config = new PluginIdeaboxConfig();
            $config->getFromDB(1);
            $title = $config->fields['title'] ?? __('Menu', 'ideabox');
            echo "<tr><th class='center'>" . $title. "</th></tr>";

            $comment = $config->fields['comment'] ?? "";
            if (!empty($comment)) {
                echo "<tr class='tab_bg_1'><td class='center'>";
                echo $comment;
                echo "</td></tr>";
            }

            echo "<tr class='tab_bg_1'><td class='center'>";
            echo "<a href=\"./ideabox.form.php\" class='submit btn btn-primary'>";
            echo "<i class='".PluginIdeaboxIdeabox::getIcon()."'></i>&nbsp;".__('Submit an idea', 'ideabox');
            echo "</a>";
            echo "</td></tr>";
            echo " </table></div>";
        }

        $_GET["field"]    = [0 => "2"];
        $_GET["contains"] = [0 => $_SESSION["glpiname"]];

        Search::showList("PluginIdeaboxIdeabox", $_GET);
    } else {
        Search::show("PluginIdeaboxIdeabox");
    }
} else {
    Html::displayRightError();
}


if (Session::getCurrentInterface() != 'central'
    && Plugin::isPluginActive('servicecatalog')) {
    PluginServicecatalogMain::showNavBarFooter('ideabox');
}

if (Session::getCurrentInterface() == 'central') {
    Html::footer();
} else {
    Html::helpFooter();
}
