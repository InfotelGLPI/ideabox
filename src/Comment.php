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
 the Free Software Foundation; either version 2 of the License, or
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

use CommonDBChild;
use CommonGLPI;
use DBConnection;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Glpi\RichText\RichText;
use Html;
use Migration;
use Notification;
use Notification_NotificationTemplate;
use NotificationEvent;
use NotificationTemplate;
use NotificationTemplateTranslation;
use Session;
use User;

class Comment extends CommonDBChild
{
    public static $rightname = "plugin_ideabox";

    public static $itemtype = Ideabox::class;
    public static $items_id = 'plugin_ideabox_ideaboxes_id';

    public static function getTypeName($nb = 0)
    {
        return _n('Comment', 'Comments', $nb, 'ideabox');
    }

    /**
     * @return string
     */
    public static function getIcon()
    {
        return "ti ti-message-circle-2";
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                return self::createTabEntry(self::getTypeName(2), self::countForIdea($item));
            }
            return self::createTabEntry(self::getTypeName(2));
        }
        return '';
    }

    public static function countForIdea(Ideabox $item)
    {
        $dbu = new DbUtils();

        $restrict = ['plugin_ideabox_ideaboxes_id' => $item->getID()];
        return $dbu->countElementsInTable(
            'glpi_plugin_ideabox_comments',
            $restrict
        );
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == Ideabox::class) {
            $self = new self();
            $self->showForm(0, ['plugin_ideabox_ideaboxes_id' => $item->getField('id')]);


            if ($_SESSION['glpiactiveprofile']['interface'] != 'central') {
                $self->seeComments($item->getField('id'), true);
            } else {
                $self->showComments($item);
            }
        }
        return true;
    }

    /**
     * Clean object veryfing criteria (when a relation is deleted)
     *
     * @param $crit array of criteria (should be an index)
     */
    public function clean($crit)
    {
        global $DB;

        foreach ($DB->request($this->getTable(), $crit) as $data) {
            $this->delete($data);
        }
    }

    public function prepareInputForAdd($input)
    {
        // Not attached to reference -> not added
        if (!isset($input['plugin_ideabox_ideaboxes_id'])
            || $input['plugin_ideabox_ideaboxes_id'] <= 0) {
            return false;
        }

        $input['users_id']     = Session::getLoginUserID();
        $input['date_comment'] = $_SESSION["glpi_currenttime"];

        if (empty($input['name'])) {
            Session::addMessageAfterRedirect(__s("The name is mandatory", "ideabox"), false, ERROR);
            return false;
        }

        if (empty($input['comment'])) {
            Session::addMessageAfterRedirect(__s("The description is mandatory", "ideabox"), false, ERROR);
            return false;
        }

        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        if (Session::getCurrentInterface() != 'central'
            && $this->fields['users_id'] != Session::getLoginUserID()) {
            Session::addMessageAfterRedirect(__s("Only original author can modify it", "ideabox"), false, ERROR);
            return false;
        }

        return $input;
    }

    public function post_addItem()
    {
        global $CFG_GLPI;

        $idea = new Ideabox();
        if ($CFG_GLPI["notifications_mailing"]) {
            $options = ['comment_id' => $this->fields["id"]];
            if ($idea->getFromDB($this->fields["plugin_ideabox_ideaboxes_id"])) {
                NotificationEvent::raiseEvent("newcomment", $idea, $options);
            }
        }
    }

    public function post_updateItem($history = 1)
    {
        global $CFG_GLPI;

        $idea = new Ideabox();
        if ($CFG_GLPI["notifications_mailing"]) {
            if (count($this->updates)) {
                $options = ['comment_id' => $this->fields["id"]];
                if ($idea->getFromDB($this->fields["plugin_ideabox_ideaboxes_id"])) {
                    NotificationEvent::raiseEvent("updatecomment", $idea, $options);
                }
            }
        }
    }

    public function pre_deleteItem()
    {
        global $CFG_GLPI;

        if (Session::getCurrentInterface() != 'central'
            && $this->fields['users_id'] != Session::getLoginUserID()) {
            Session::addMessageAfterRedirect(__s("Only original author can modify it", "ideabox"), false, ERROR);
            return false;
        }

        $idea = new Ideabox();
        if ($CFG_GLPI["notifications_mailing"]) {
            $options = ['comment_id' => $this->fields["id"]];
            if ($idea->getFromDB($this->fields["plugin_ideabox_ideaboxes_id"])) {
                NotificationEvent::raiseEvent("deletecomment", $idea, $options);
            }
        }
        return true;
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        if ($ID < 1) {
            $options['users_id']     = Session::getLoginUserID();
            $options['date_comment'] = $_SESSION["glpi_currenttime"];
        } else {
            $options['users_id']     = $this->fields['users_id'];
            $options['date_comment'] = $this->fields['date_comment'];
        }
        TemplateRenderer::getInstance()->display('@ideabox/comment_form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }

    public function seeComments($ID, $fromidea)
    {
        global $DB;

        $parent = new Ideabox();
        if (!$parent->getFromDB($ID) || !$parent->can($ID, READ)) {
            echo __s('Access denied');
            return;
        }

        $raw_comments = [];
        foreach ($DB->request([
            'SELECT'  => '*',
            'FROM'    => 'glpi_plugin_ideabox_comments',
            'WHERE'   => ['plugin_ideabox_ideaboxes_id' => $ID],
            'ORDERBY' => 'date_comment DESC',
        ]) as $row) {
            $raw_comments[] = $row;
        }

        if (empty($raw_comments)) {
            return;
        }

        $comments = [];
        foreach ($raw_comments as $row) {
            $user = new User();
            $user->getFromDB($row['users_id']);
            $thumbnail_url = User::getThumbnailURLForPicture($user->fields['picture']);

            $delete_form = '';
            if ($row['users_id'] == Session::getLoginUserID()) {
                ob_start();
                Html::showSimpleForm(
                    (new self())->getFormURL(),
                    'purge',
                    _sx('button', 'Delete', 'ideabox'),
                    ['id' => $row['id']],
                    'ti-trash',
                    "class='btn btn-sm btn-ghost-danger'"
                );
                $delete_form = ob_get_clean();
            }

            $comments[] = [
                'avatar_style'  => !empty($thumbnail_url)
                    ? "background-image:url('" . htmlspecialchars($thumbnail_url, ENT_QUOTES) . "')"
                    : 'background-color:' . htmlspecialchars($user->getUserInitialsBgColor(), ENT_QUOTES),
                'user_initials' => htmlspecialchars($user->getUserInitials(), ENT_QUOTES),
                'has_thumbnail' => !empty($thumbnail_url),
                'user_name'     => htmlspecialchars(
                    formatUserName($user->getID(), $user->fields['name'], $user->fields['realname'], $user->fields['firstname']),
                    ENT_QUOTES
                ),
                'user_name_link' => getUserName($row['users_id'], 0, true),
                'date_relative'  => Html::timestampToRelativeStr($row['date_comment']),
                'text'           => RichText::getEnhancedHtml($row['comment']),
                'can_delete'     => $row['users_id'] == Session::getLoginUserID(),
                'delete_form'    => $delete_form,
            ];
        }

        $add_comment_form = '';
        if (!$fromidea) {
            ob_start();
            Html::showSimpleForm(
                (new self())->getFormURL() . '?in_modal=1&plugin_ideabox_ideaboxes_id=' . $ID,
                'addcomment',
                _sx('button', 'Post a comment', 'ideabox'),
                ['plugin_ideabox_ideaboxes_id' => $ID],
                'ti-send',
                "class='btn btn-primary btn-sm'"
            );
            $add_comment_form = ob_get_clean();
        }

        TemplateRenderer::getInstance()->display('@ideabox/comments_list.html.twig', [
            'comments'         => $comments,
            'count'            => count($comments),
            'label_comments'   => _n('comment', 'comments', count($comments), 'ideabox'),
            'from_idea'        => $fromidea,
            'add_comment_form' => $add_comment_form,
        ]);
    }


    /**
     * @return array
     */
    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'   => 'common',
            'name' => self::getTypeName(2),
        ];

        $tab[] = [
            'id'            => '1',
            'table'         => $this->getTable(),
            'field'         => 'name',
            'name'          => __s('Name'),
            'datatype'      => 'itemlink',
            'itemlink_type' => $this->getType(),
        ];

        $tab[] = [
            'id'            => '7',
            'table'         => $this->getTable(),
            'field'         => 'date_comment',
            'name'          => __s('Date of comment', 'ideabox'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id'       => '10',
            'table'    => 'glpi_users',
            'field'    => 'name',
            'name'     => __s('Author'),
            'datatype' => 'dropdown',
            'right'    => 'all',
        ];

        $tab[] = [
            'id'       => '8',
            'table'    => $this->getTable(),
            'field'    => 'comment',
            'name'     => __s('Description', 'ideabox'),
            'datatype' => 'text',
        ];


        $tab[] = [
            'id'       => '30',
            'table'    => $this->getTable(),
            'field'    => 'id',
            'name'     => __s('ID'),
            'datatype' => 'number',
        ];


        return $tab;
    }

    /**
     * @since version 0.84
     **/
    public function getForbiddenStandardMassiveAction()
    {
        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

    public function showComments(Ideabox $ideabox)
    {
        global $DB, $CFG_GLPI;

        $instID = $ideabox->fields['id'];

        if (!$ideabox->can($instID, READ)) {
            return false;
        }

        $rand    = mt_rand();
        $canedit = $ideabox->can($instID, UPDATE);

        $iterator = $DB->request([
            'SELECT'    => [
                'glpi_plugin_ideabox_comments.name AS name',
                'glpi_plugin_ideabox_comments.id',
                'glpi_plugin_ideabox_comments.plugin_ideabox_ideaboxes_id',
                'glpi_plugin_ideabox_comments.date_comment',
                'glpi_plugin_ideabox_comments.comment',
                'glpi_plugin_ideabox_comments.users_id AS users_id',
            ],
            'FROM'      => 'glpi_plugin_ideabox_comments',
            'LEFT JOIN'       => [
                'glpi_plugin_ideabox_ideaboxes' => [
                    'ON' => [
                        'glpi_plugin_ideabox_ideaboxes' => 'id',
                        'glpi_plugin_ideabox_comments'  => 'plugin_ideabox_ideaboxes_id'
                    ]
                ]
            ],
            'WHERE'     => [
                'glpi_plugin_ideabox_comments.plugin_ideabox_ideaboxes_id'  => $instID
            ],
            'ORDERBY'   => 'glpi_plugin_ideabox_comments.name',
        ]);

        $number = count($iterator);

        echo "<div class='spaced'>";

        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams
               = ['num_displayed'    => min($_SESSION['glpilist_limit'], $number),
                   'specific_actions' => ['purge' => _x('button', 'Delete permanently')],
                   'container'        => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
        }

        if ($number != 0) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr>";

            if ($canedit && $number) {
                echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
            } else {
                echo "<th width='10'></th>";
            }

            echo "<th>" . __s('Name') . "</th>";
            echo "<th>" . __s('Author') . "</th>";
            echo "<th>" . __s('Date') . "</th>";
            echo "<th>" . __s('Description', 'ideabox') . "</th>";

            echo "</tr>";

            Session::initNavigateListItems($this->getType(), Ideabox::getTypeName(2) . " = " . $ideabox->fields["name"]);
            $i       = 0;
            $row_num = 1;

            foreach ($iterator as $data) {
                Session::addToNavigateListItems($this->getType(), $data['id']);

                $i++;
                $row_num++;
                echo "<tr class='tab_bg_1 center'>";
                echo "<td width='10'>";
                if ($canedit) {
                    Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
                }
                echo "</td>";

                echo "<td class='left'>";
                echo "<a href='" . $CFG_GLPI["root_doc"] . "/plugins/ideabox/front/comment.form.php?id=" . $data["id"] . "&amp;plugin_ideabox_ideaboxes_id=" . $data["plugin_ideabox_ideaboxes_id"] . "'>";
                echo htmlescape($data["name"]);
                if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                    echo " (" . $data["id"] . ")";
                }
                echo "</a></td>";

                echo "<td class='left'>" . getusername($data["users_id"]) . "</td>";
                echo "<td class='left'>" . Html::convdatetime($data["date_comment"]) . "</td>";
                echo "<td class='left'>" . RichText::getTextFromHtml($data["comment"]) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        if ($canedit && $number) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }

    public static function install(Migration $migration)
    {
        global $DB;

        $default_charset   = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();
        $table  = self::getTable();

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                        `id` int {$default_key_sign} NOT NULL auto_increment,
                        `name` varchar(255) collate utf8mb4_unicode_ci DEFAULT NULL,
                        `date_comment` timestamp DEFAULT NULL,
                        `plugin_ideabox_ideaboxes_id` int {$default_key_sign} NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_ideabox_ideaboxes (id)',
                        `users_id` int {$default_key_sign} NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_users (id)',
                        `comment` text collate utf8mb4_unicode_ci,
                        PRIMARY KEY  (`id`),
                        KEY `name` (`name`),
                        KEY `plugin_ideabox_ideaboxes_id` (`plugin_ideabox_ideaboxes_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

            $DB->doQuery($query);

            $DB->insert(
                'glpi_displaypreferences',
                ['itemtype' => self::class,
                    'num' => 1,
                    'rank' => 1,
                    'users_id' => 0,
                    'interface' => 'central']
            );

            $DB->insert(
                'glpi_displaypreferences',
                ['itemtype' => self::class,
                    'num' => 7,
                    'rank' => 2,
                    'users_id' => 0,
                    'interface' => 'central']
            );

            $DB->insert(
                'glpi_displaypreferences',
                ['itemtype' => self::class,
                    'num' => 10,
                    'rank' => 3,
                    'users_id' => 0,
                    'interface' => 'central']
            );
        }

        //DisplayPreferences Migration
        $classes = ['PluginIdeaboxComment' => self::class];

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
    }

    public static function uninstall()
    {
        global $DB;

        $DB->dropTable(self::getTable(), true);

        $notif = new Notification();

        $options = ['itemtype' => self::class];
        foreach ($DB->request([
            'FROM' => 'glpi_notifications',
            'WHERE' => $options]) as $data) {
            $notif->delete($data);
        }

        //templates
        $template       = new NotificationTemplate();
        $translation    = new NotificationTemplateTranslation();
        $notif_template = new Notification_NotificationTemplate();

        $options        = ['itemtype' => self::class];
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
            $item->deleteByCriteria(['itemtype' => self::class]);
        }
    }
}
