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
use GlpiPlugin\Ideabox\Ideabox;
use GlpiPlugin\Ideabox\IdeaboxInjection;
use GlpiPlugin\Ideabox\Profile;

function plugin_ideabox_install()
{
    global $DB;

    if (!$DB->tableExists("glpi_plugin_ideabox_ideaboxes")) {
        $DB->runFile(PLUGIN_IDEABOX_DIR . "/sql/empty-4.0.0.sql");

        install_notifications_ideabox();

    } elseif (!$DB->tableExists("glpi_plugin_ideabox_votes")) {
        $DB->runFile(PLUGIN_IDEABOX_DIR . "/sql/update-3.0.0.sql");
    }

    $DB->runFile(PLUGIN_IDEABOX_DIR . "/sql/update-4.0.0.sql");

    Profile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
    return true;
}

function install_notifications_ideabox()
{

    global $DB;

    $migration = new Migration(1.0);

    // Notification
    // Request
    $options_notif        = ['itemtype' => Ideabox::class,
        'name' => 'Idea'];
    $DB->insert(
        "glpi_notificationtemplates",
        $options_notif
    );

    foreach ($DB->request([
        'FROM' => 'glpi_notificationtemplates',
        'WHERE' => $options_notif]) as $data) {
        $templates_id = $data['id'];

        if ($templates_id) {

            $DB->insert(
                "glpi_notificationtemplatetranslations",
                [
                    'notificationtemplates_id' => $templates_id,
                    'subject' => '##lang.ideabox.title##',
                    'content_text' => '##lang.ideabox.url## : ##ideabox.url##
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
                    'content_html' => '&lt;p&gt;&lt;strong&gt;##lang.ideabox.url##&lt;/strong&gt; : &lt;a href=\"##ideabox.url##\"&gt;##ideabox.url##&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;##lang.ideabox.entity##&lt;/strong&gt; : ##ideabox.entity##&lt;br /&gt; ##IFideabox.name##&lt;strong&gt;##lang.ideabox.name##&lt;/strong&gt; : ##ideabox.name####ENDIFideabox.name##&lt;br /&gt;&lt;br /&gt; ##IFideabox.comment##&lt;strong&gt;##lang.ideabox.comment##&lt;/strong&gt; : ##ideabox.comment####ENDIFideabox.comment##&lt;br /&gt;&lt;br /&gt;##FOREACHupdates##----------&lt;br /&gt;&lt;strong&gt;##lang.update.title## :&lt;/strong&gt;&lt;br /&gt;##IFupdate.name##&lt;strong&gt;##lang.ideabox.name##&lt;/strong&gt; : ##update.name####ENDIFupdate.name##&lt;br /&gt;##IFupdate.comment##&lt;br /&gt;&lt;strong&gt;##lang.ideabox.comment##&lt;/strong&gt; : ##update.comment##&lt;br /&gt;##ENDIFupdate.comment##&lt;br /&gt;----------##ENDFOREACHupdates##&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;##lang.comment.title## :&lt;/strong&gt;&lt;br /&gt;----------&lt;br /&gt;##FOREACHcomments####IFcomment.name##&lt;strong&gt;##lang.comment.name##&lt;/strong&gt; : ##comment.name####ENDIFcomment.name##&lt;br /&gt;##IFcomment.author##&lt;strong&gt;##lang.comment.author##&lt;/strong&gt; : ##comment.author####ENDIFcomment.author##&lt;br /&gt;##IFcomment.datecomment##&lt;strong&gt;##lang.comment.datecomment##&lt;/strong&gt; : ##comment.datecomment####ENDIFcomment.datecomment##&lt;br /&gt;##IFcomment.comment##&lt;strong&gt;##lang.comment.comment##&lt;/strong&gt; : ##comment.comment####ENDIFcomment.comment##&lt;br /&gt;----------&lt;br /&gt;##ENDFOREACHcomments##&lt;/p&gt;',
                ]
            );

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'New Idea',
                    'entities_id' => 0,
                    'itemtype' => Ideabox::class,
                    'event' => 'new',
                    'is_recursive' => 1,
                ]
            );
            $options_notif        = ['itemtype' => Ideabox::class,
                'name' => 'New Idea',
                'event' => 'new'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'Update Idea',
                    'entities_id' => 0,
                    'itemtype' => Ideabox::class,
                    'event' => 'update',
                    'is_recursive' => 1,
                ]
            );
            $options_notif        = ['itemtype' => Ideabox::class,
                'name' => 'Update Idea',
                'event' => 'update'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'Delete Idea',
                    'entities_id' => 0,
                    'itemtype' => Ideabox::class,
                    'event' => 'delete',
                    'is_recursive' => 1,
                ]
            );
            $options_notif        = ['itemtype' => Ideabox::class,
                'name' => 'Delete Idea',
                'event' => 'delete'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'New comment of idea',
                    'entities_id' => 0,
                    'itemtype' => Comment::class,
                    'event' => 'newcomment',
                    'is_recursive' => 1,
                ]
            );
            $options_notif        = ['itemtype' => Comment::class,
                'name' => 'New comment of Idea',
                'event' => 'newcomment'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'Update comment of idea',
                    'entities_id' => 0,
                    'itemtype' => Comment::class,
                    'event' => 'updatecomment',
                    'is_recursive' => 1,
                ]
            );
            $options_notif        = ['itemtype' => Comment::class,
                'name' => 'Update comment of idea',
                'event' => 'updatecomment'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'Delete comment of idea',
                    'entities_id' => 0,
                    'itemtype' => Comment::class,
                    'event' => 'deletecomment',
                    'is_recursive' => 1,
                ]
            );
            $options_notif        = ['itemtype' => Comment::class,
                'name' => 'Delete comment of idea',
                'event' => 'deletecomment'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }
        }
    }

    $migration->executeMigration();

    //DisplayPreferences Migration
    $classes = ['PluginIdeaboxIdeabox' => Ideabox::class,
        'PluginIdeaboxComment' => Comment::class];

    foreach ($classes as $old => $new) {
        $displayusers = $DB->request([
            'SELECT' => [
                'users_id'
            ],
            'DISTINCT' => true,
            'FROM' => 'glpi_displaypreferences',
            'WHERE' => [
                'itemtype' => $old,
            ],
        ]);

        if (count($displayusers) > 0) {
            foreach ($displayusers as $displayuser) {
                $iterator = $DB->request([
                    'SELECT' => [
                        'num',
                        'id'
                    ],
                    'FROM' => 'glpi_displaypreferences',
                    'WHERE' => [
                        'itemtype' => $old,
                        'users_id' => $displayuser['users_id'],
                        'interface' => 'central'
                    ],
                ]);

                if (count($iterator) > 0) {
                    foreach ($iterator as $data) {
                        $iterator2 = $DB->request([
                            'SELECT' => [
                                'id'
                            ],
                            'FROM' => 'glpi_displaypreferences',
                            'WHERE' => [
                                'itemtype' => $new,
                                'users_id' => $displayuser['users_id'],
                                'num' => $data['num'],
                                'interface' => 'central'
                            ],
                        ]);
                        if (count($iterator2) > 0) {
                            foreach ($iterator2 as $dataid) {
                                $query = $DB->buildDelete(
                                    'glpi_displaypreferences',
                                    [
                                        'id' => $dataid['id'],
                                    ]
                                );
                                $DB->doQuery($query);
                            }
                        } else {
                            $query = $DB->buildUpdate(
                                'glpi_displaypreferences',
                                [
                                    'itemtype' => $new,
                                ],
                                [
                                    'id' => $data['id'],
                                ]
                            );
                            $DB->doQuery($query);
                        }
                    }
                }
            }
        }
    }
    return true;
}

function plugin_ideabox_uninstall()
{
    global $DB;

    $tables = ["glpi_plugin_ideabox_ideaboxs",
        "glpi_plugin_ideabox_ideaboxes",
        "glpi_plugin_ideabox_comments",
        "glpi_plugin_ideabox_votes",
        "glpi_plugin_ideabox_configs",
        "glpi_plugin_ideabox_configtranslations"];

    foreach ($tables as $table) {
        $DB->dropTable($table, true);
    }

    $notif = new Notification();

    $options = ['itemtype' => Ideabox::class];
    foreach ($DB->request([
        'FROM' => 'glpi_notifications',
        'WHERE' => $options]) as $data) {
        $notif->delete($data);
    }

    $options = ['itemtype' => Comment::class];
    foreach ($DB->request([
        'FROM' => 'glpi_notifications',
        'WHERE' => $options]) as $data) {
        $notif->delete($data);
    }

    //templates
    $template       = new NotificationTemplate();
    $translation    = new NotificationTemplateTranslation();
    $notif_template = new Notification_NotificationTemplate();
    $options        = ['itemtype' => Ideabox::class];
    foreach ($DB->request([
        'FROM' => 'glpi_notificationtemplates',
        'WHERE' => $options]) as $data) {
        $options_template = [
            'notificationtemplates_id' => $data['id'],
        ];

        foreach ($DB->request([
            'FROM' => 'glpi_notificationtemplatetranslations',
            'WHERE' => $options_template]) as $data_template) {
            $translation->delete($data_template);
        }
        $template->delete($data);

        foreach ($DB->request([
            'FROM' => 'glpi_notifications_notificationtemplates',
            'WHERE' => $options_template]) as $data_template) {
            $notif_template->delete($data_template);
        }
    }
    $options        = ['itemtype' => Comment::class];
    foreach ($DB->request([
        'FROM' => 'glpi_notificationtemplates',
        'WHERE' => $options]) as $data) {
        $options_template = [
            'notificationtemplates_id' => $data['id'],
        ];

        foreach ($DB->request([
            'FROM' => 'glpi_notificationtemplatetranslations',
            'WHERE' => $options_template]) as $data_template) {
            $translation->delete($data_template);
        }
        $template->delete($data);

        foreach ($DB->request([
            'FROM' => 'glpi_notifications_notificationtemplates',
            'WHERE' => $options_template]) as $data_template) {
            $notif_template->delete($data_template);
        }
    }

    $itemtypes = ['Alert',
        'DisplayPreference',
        'Document_Item',
        'ImpactItem',
        'Item_Ticket',
        'Link_Itemtype',
        'Notepad',
        'SavedSearch',
        'DropdownTranslation',
        'NotificationTemplate',
        'Notification'];
    foreach ($itemtypes as $itemtype) {
        $item = new $itemtype();
        $item->deleteByCriteria(['itemtype' => Ideabox::class]);

        $item = new $itemtype();
        $item->deleteByCriteria(['itemtype' => Comment::class]);
    }

    if (class_exists('PluginDatainjectionModel')) {
        PluginDatainjectionModel::clean(['itemtype' => Ideabox::class]);
    }

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
        return ["glpi_entities"                 => ["glpi_plugin_ideabox_ideaboxes" => "entities_id"],
            "glpi_users"                    => ["glpi_plugin_ideabox_ideaboxes" => "users_id",
                "glpi_plugin_ideabox_comments"  => "users_id"],
            //                "glpi_plugin_ideabox_ideaboxes" => ["glpi_plugin_ideabox_comments" => "plugin_ideabox_ideaboxes_id"]
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
