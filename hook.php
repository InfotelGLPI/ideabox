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

use GlpiPlugin\Ideabox\Comment;
use GlpiPlugin\Ideabox\Config;
use GlpiPlugin\Ideabox\ConfigTranslation;
use GlpiPlugin\Ideabox\Ideabox;
use GlpiPlugin\Ideabox\IdeaboxInjection;
use GlpiPlugin\Ideabox\Profile;
use GlpiPlugin\Ideabox\Vote;

function plugin_ideabox_install()
{
    global $DB;

    $migration = new Migration(PLUGIN_IDEABOX_VERSION);

    Ideabox::install($migration);
    Comment::install($migration);
    Vote::install($migration);
    Config::install($migration);
    ConfigTranslation::install($migration);

    $DB->runFile(PLUGIN_IDEABOX_DIR . "/sql/update-4.0.3.sql");

    Profile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

    return true;
}


function plugin_ideabox_uninstall()
{
    Ideabox::uninstall();
    Comment::uninstall();
    Vote::uninstall();
    Config::uninstall();
    ConfigTranslation::uninstall();

    return true;
}

function plugin_ideabox_AssignToTicket($types)
{
    if (Session::haveRight("plugin_ideabox_open_ticket", "1")) {
        $types[Ideabox::class] = Ideabox::getTypeName(2);
    }

    return $types;
}

// Define dropdown relations
function plugin_ideabox_getDatabaseRelations()
{
    if (Plugin::isPluginActive("ideabox")) {
        return [
            "glpi_entities" => ["glpi_plugin_ideabox_ideaboxes" => "entities_id"],
            "glpi_users" => [
                "glpi_plugin_ideabox_ideaboxes" => "users_id",
                "glpi_plugin_ideabox_comments" => "users_id"
            ],
        ];
    } else {
        return [];
    }
}

/**
 * @param $type
 *
 * @return string
 */
function plugin_ideabox_addDefaultWhere($type)
{
    switch ($type) {
        case Ideabox::class:
            $who = Session::getLoginUserID();
            if (Session::getCurrentInterface() != 'central') {
                return " `glpi_plugin_ideabox_ideaboxes`.`users_id` = '$who' ";
            }
    }
    return "";
}

function plugin_datainjection_populate_ideabox()
{
    global $INJECTABLE_TYPES;
    $INJECTABLE_TYPES[IdeaboxInjection::class] = 'ideabox';
}
