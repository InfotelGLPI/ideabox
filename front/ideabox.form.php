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

use GlpiPlugin\Ideabox\Ideabox;
use GlpiPlugin\Ideabox\Vote;

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}
$idea = new Ideabox();

if (isset($_POST["add"])) {
    $idea->check(-1, CREATE, $_POST);
    $newID = $idea->add($_POST);
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($idea->getFormURL() . "?id=" . $newID);
    }
    Html::back();
} elseif (isset($_POST["delete"])) {
    $idea->check($_POST['id'], DELETE);
    $idea->delete($_POST);
    $idea->redirectToList();
} elseif (isset($_POST["restore"])) {
    $idea->check($_POST['id'], PURGE);
    $idea->restore($_POST);
    $idea->redirectToList();
} elseif (isset($_POST["purge"])) {
    $idea->check($_POST['id'], PURGE);
    $idea->delete($_POST, 1);
    $idea->redirectToList();
} elseif (isset($_POST["update"])) {
    $idea->check($_POST['id'], UPDATE);
    $idea->update($_POST);
    Html::back();
} elseif (isset($_POST["vote"])) {
    $idea->check($_POST['id'], UPDATE);
    $vote = new Vote();
    $vote->add(['users_id' => Session::getLoginUserID(),
       'date_vote' =>$_SESSION["glpi_currenttime"],
       'plugin_ideabox_ideaboxes_id' => $_POST['id']]);
    Html::back();
} elseif (isset($_POST["cancelvote"])) {
    $idea->check($_POST['id'], UPDATE);
    $vote = new Vote();
    $vote->deleteByCriteria(['users_id' => Session::getLoginUserID(),
       'plugin_ideabox_ideaboxes_id' => $_POST['id']]);
    Html::back();
} else {
    $idea->checkGlobal(READ);

    if (isset($_POST["addcomment"])) {
        $_GET['id'] = $_POST["plugin_ideabox_ideaboxes_id"];
    }
    if (Session::getCurrentInterface() == 'central') {
        Html::header(Ideabox::getTypeName(2), '', "tools", Ideabox::class);
    } else {
        if (Plugin::isPluginActive('servicecatalog')) {
            PluginServicecatalogMain::showDefaultHeaderHelpdesk(Ideabox::getTypeName(2), true);
        } else {
            Html::helpHeader(Ideabox::getTypeName(2));
        }
    }

    $idea->display($_GET);

    if (Session::getCurrentInterface() != 'central'
       && Plugin::isPluginActive('servicecatalog')) {
        PluginServicecatalogMain::showNavBarFooter('ideabox');
    }

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}
