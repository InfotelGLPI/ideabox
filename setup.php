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

define('PLUGIN_IDEABOX_VERSION', '4.0.0');

global $CFG_GLPI;

use Glpi\Plugin\Hooks;

if (!defined("PLUGIN_IDEABOX_DIR")) {
    define("PLUGIN_IDEABOX_DIR", Plugin::getPhpDir("ideabox"));
    define("PLUGIN_IDEABOX_NOTFULL_DIR", Plugin::getPhpDir("ideabox", false));
    $root = $CFG_GLPI['root_doc'] . '/plugins/ideabox';
    define("PLUGIN_IDEABOX_WEBDIR", $root);
}

// Init the hooks of the plugins -Needed
function plugin_init_ideabox()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['ideabox'] = true;
    $PLUGIN_HOOKS['change_profile']['ideabox']   = ['PluginIdeaboxProfile', 'initProfile'];
    $PLUGIN_HOOKS['plugin_datainjection_populate']['ideabox'] = 'plugin_datainjection_populate_ideabox';
    $PLUGIN_HOOKS['assign_to_ticket']['ideabox'] = true;
    $PLUGIN_HOOKS[Hooks::ADD_CSS]['ideabox'] = "css/ideabox.css";
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['ideabox'][] = 'lib/fuse.js';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['ideabox'][] = 'lib/fuzzysearch.js.php';

    if (Session::getLoginUserID()) {
        Plugin::registerClass('PluginIdeaboxIdeabox', [
           'assignable_types'              => true,
           'document_types'              => true,
           'helpdesk_visible_types'      => true,
           'ticket_types'                => true,
           'notificationtemplates_types' => true
        ]);

        Plugin::registerClass('PluginIdeaboxComment', [
           'notificationtemplates_types' => true
        ]);

        Plugin::registerClass(
            'PluginIdeaboxProfile',
            ['addtabon' => 'Profile']
        );

        if (Session::haveRight("config", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['ideabox'] = 'front/config.form.php';
        }

        // Display a menu entry ?
        if (Session::haveRight("plugin_ideabox", READ)) {
            $PLUGIN_HOOKS['menu_toadd']['ideabox'] = ['tools' => PluginIdeaboxIdeabox::getType()];

            if (!Plugin::isPluginActive('servicecatalog')) {
                $PLUGIN_HOOKS['helpdesk_menu_entry']['ideabox'] = PLUGIN_IDEABOX_NOTFULL_DIR.'/front/ideabox.php';
                $PLUGIN_HOOKS['helpdesk_menu_entry_icon']['ideabox'] = 'ti ti-bulb';
            }

            if (Plugin::isPluginActive('servicecatalog')) {
                $PLUGIN_HOOKS['servicecatalog']['ideabox'] = ['PluginIdeaboxServicecatalog'];
            }

            $PLUGIN_HOOKS['redirect_page']['ideabox']           = PLUGIN_IDEABOX_NOTFULL_DIR.'/front/ideabox.php';
        }

        if (Session::haveRight("plugin_ideabox", UPDATE)) {
            $PLUGIN_HOOKS['use_massive_action']['ideabox']   = 1;
        }

        $PLUGIN_HOOKS['migratetypes']['ideabox'] = 'plugin_datainjection_migratetypes_ideabox';
    }
}


// Get the name and the version of the plugin - Needed

/**
 * @return array
 */
function plugin_version_ideabox()
{
    return [
       'name'         => _n('Idea box', 'Ideas box', 2, 'ideabox'),
       'version'      => PLUGIN_IDEABOX_VERSION,
       'author'       => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
       'license'      => 'GPLv2+',
       'homepage'     => 'https://github.com/InfotelGLPI/ideabox',
       'requirements' => [
          'glpi' => [
             'min' => '11.0',
             'max' => '12.0',
             'dev' => false
          ]
       ]
    ];
}

function plugin_datainjection_migratetypes_ideabox($types)
{
    $types[4900] = 'PluginIdeaboxIdeabox';
    return $types;
}
