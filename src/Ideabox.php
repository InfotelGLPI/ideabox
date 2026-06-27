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

use Ajax;
use CommonDBTM;
use DBConnection;
use Glpi\Application\View\TemplateRenderer;
use Glpi\RichText\RichText;
use Html;
use Migration;
use Notification;
use Notification_NotificationTemplate;
use NotificationEvent;
use NotificationTemplate;
use NotificationTemplateTranslation;
use PluginDatainjectionModel;
use Session;
use User;

class Ideabox extends CommonDBTM
{
    public $dohistory  = true;
    public static $rightname  = "plugin_ideabox";
    protected $usenotepad = true;

    public const NEW = 1;
    public const STUDY = 2;
    public const IN_PROGRESS = 3;
    public const CLOSED = 4;

    public static function getTypeName($nb = 0)
    {
        return _n('Idea', 'Ideas', $nb, 'ideabox');
    }

    /**
     * @return string
     */
    public static function getIcon()
    {
        return "ti ti-bulb";
    }

    /**
     * @return bool|int
     */
    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ);
    }

    /**
     * @return bool
     */
    public static function canCreate(): bool
    {
        return Session::haveRight(self::$rightname, CREATE);
    }

    /**
     * @return bool
     */
    public static function canUpdate(): bool
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public function canUpdateItem(): bool
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    //clean if ideabox are deleted
    public function cleanDBonPurge()
    {
        $temp = new Comment();
        $temp->deleteByCriteria(['plugin_ideabox_ideaboxes_id' => $this->fields['id']]);

        $temp = new Vote();
        $temp->deleteByCriteria(['plugin_ideabox_ideaboxes_id' => $this->fields['id']]);
    }

    /**
     * @return array
     */
    public static function getMenuContent()
    {

        $menu                    = [];
        $menu['title']           = self::getMenuName();
        $menu['page']            = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);
        if (Session::haveRight(static::$rightname, UPDATE)
            || Session::haveRight("config", UPDATE)) {
            $menu['links']['add'] = self::getFormURL(false);
            $menu['links']['config'] = Config::getFormURL(false);
        }

        $menu['icon']    = self::getIcon();

        return $menu;
    }


    public static function removeRightsFromSession()
    {
        if (isset($_SESSION['glpimenu']['tools']['types'][Ideabox::class])) {
            unset($_SESSION['glpimenu']['tools']['types'][Ideabox::class]);
        }
        if (isset($_SESSION['glpimenu']['tools']['content'][Ideabox::class])) {
            unset($_SESSION['glpimenu']['tools']['content'][Ideabox::class]);
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab(Comment::class, $ong, $options);
        if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $this->addStandardTab('Item_Ticket', $ong, $options);
            $this->addStandardTab('Item_Problem', $ong, $options);
            $this->addStandardTab('Document_Item', $ong, $options);
            $this->addStandardTab('Note', $ong, $options);
            $this->addStandardTab('Log', $ong, $options);
        }

        return $ong;
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
            'field'         => 'date_idea',
            'name'          => __s('Date of submission', 'ideabox'),
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
            'id'       => '9',
            'table'    => $this->getTable(),
            'field'    => 'is_helpdesk_visible',
            'name'     => __s('Associable to a ticket'),
            'datatype' => 'bool',
        ];

        $tab[] = [
            'id'       => '30',
            'table'    => $this->getTable(),
            'field'    => 'id',
            'name'     => __s('ID'),
            'datatype' => 'number',
        ];

        $tab[] = [
            'id'       => '80',
            'table'    => 'glpi_entities',
            'field'    => 'completename',
            'name'     => __s('Entity'),
            'datatype' => 'dropdown',
        ];

        return $tab;
    }


    /**
     * @return void
     */
    public function post_addItem()
    {
        global $CFG_GLPI;

        if ($CFG_GLPI["notifications_mailing"]) {
            NotificationEvent::raiseEvent("new", $this);
        }
    }

    public function prepareInputForAdd($input)
    {
        $input['users_id'] = Session::getLoginUserID();
        $input['date_idea'] = $_SESSION["glpi_currenttime"];

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

    public function post_updateItem($history = 1)
    {
        global $CFG_GLPI;

        if (count($this->updates)) {
            if ($CFG_GLPI["notifications_mailing"]) {
                NotificationEvent::raiseEvent('update', $this);
            }
        }
    }

    public function pre_deleteItem()
    {
        global $CFG_GLPI;

        if ($CFG_GLPI["notifications_mailing"]) {
            NotificationEvent::raiseEvent("delete", $this);
        }

        return true;
    }

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);

        if ($ID < 1) {
            $options['users_id'] = Session::getLoginUserID();
            $options['date_idea'] = $_SESSION["glpi_currenttime"];
        } else {
            $options['users_id'] = $this->fields['users_id'];
            $options['date_idea'] = $this->fields['date_idea'];
        }
        Html::requireJs("tinymce");

        TemplateRenderer::getInstance()->display('@ideabox/ideabox_form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }


    /**
     * Returns the status-related title
     *
     * @param $state
     *
     * @return string
     */
    public static function getStateName($state)
    {
        switch ($state) {

            case self::STUDY:
                return __s('In study', 'ideabox');
            case self::IN_PROGRESS:
                return __s('In progress', 'ideabox');
            case self::CLOSED:
                return __s('Closed', 'ideabox');
            default:
                return __s('New', 'ideabox');
        }
    }

    public static function getStateColor($state)
    {
        switch ($state) {

            case self::STUDY:
                return "#D1A712";
            case self::IN_PROGRESS:
                return "#4DAA77";
            case self::CLOSED:
                return "#d5703b";
            default:
                return "#2d98b1";
        }
    }

    public static function showList($params)
    {
        global $DB;

        $criteria = [
            'SELECT'  => 'id',
            'FROM'    => 'glpi_plugin_ideabox_ideaboxes',
            'WHERE'   => ['is_deleted' => 0],
            'ORDERBY' => 'date_idea DESC',
        ];

        if (isset($params['id'])) {
            $criteria['WHERE']['id'] = $params['id'];
        }
        $criteria['WHERE'] += getEntitiesRestrictCriteria('glpi_plugin_ideabox_ideaboxes');

        $iterator = $DB->request($criteria);
        if (count($iterator) === 0) {
            return;
        }

        $ideas = [];
        foreach ($iterator as $row) {
            $idea = new self();
            $idea->getFromDB($row['id']);
            $id = $idea->getID();

            $comments_raw = [];
            foreach ($DB->request([
                'SELECT'  => '*',
                'FROM'    => 'glpi_plugin_ideabox_comments',
                'WHERE'   => ['plugin_ideabox_ideaboxes_id' => $id],
                'ORDERBY' => 'date_comment ASC',
            ]) as $c) {
                $comments_raw[] = $c;
            }

            $votes_count = count($DB->request([
                'SELECT' => 'id',
                'FROM'   => 'glpi_plugin_ideabox_votes',
                'WHERE'  => ['plugin_ideabox_ideaboxes_id' => $id],
            ]));

            $user = new User();
            $user->getFromDB($idea->fields['users_id']);
            $thumbnail_url = User::getThumbnailURLForPicture($user->fields['picture']);
            $avatar_style = !empty($thumbnail_url)
                ? "background-image:url('" . htmlspecialchars($thumbnail_url, ENT_QUOTES) . "')"
                : 'background-color:' . htmlspecialchars($user->getUserInitialsBgColor(), ENT_QUOTES);

            $already_voted = (new Vote())->getFromDBByCrit([
                'users_id'                    => Session::getLoginUserID(),
                'plugin_ideabox_ideaboxes_id' => $id,
            ]);

            ob_start();
            Html::showSimpleForm(
                $idea->getFormURL(),
                $already_voted ? 'cancelvote' : 'vote',
                $already_voted ? _x('button', 'Cancel', 'ideabox') : $votes_count,
                ['id' => $id],
                $already_voted ? 'ti-circle-x' : 'ti-thumb-up',
                "class='btn btn-sm " . ($already_voted ? 'btn-ghost-danger' : 'btn-ghost-success') . "'"
            );
            $vote_form = ob_get_clean();

            ob_start();
            Html::showSimpleForm(
                '',
                'suscribe',
                _x('button', 'Suscribe', 'ideabox'),
                ['id' => $id],
                'ti-mail',
                "class='btn btn-sm btn-ghost-secondary'"
            );
            $subscribe_form = ob_get_clean();

            $comments_modal   = '';
            $add_comment_form = '';
            if (count($comments_raw) > 0) {
                $comments_modal = Ajax::createIframeModalWindow(
                    'seecomments' . $id,
                    PLUGIN_IDEABOX_WEBDIR . '/front/comment.php?plugin_ideabox_ideaboxes_id=' . $id,
                    ['title' => __s('See comments', 'ideabox'), 'display' => false, 'reloadonclose' => true]
                );
            } else {
                ob_start();
                Html::showSimpleForm(
                    $idea->getFormURL() . '?forcetab=GlpiPlugin\Ideabox\Comment$1&id=' . $id,
                    'addcomment',
                    '',
                    ['plugin_ideabox_ideaboxes_id' => $id],
                    'ti-message-plus',
                    "class='btn btn-sm btn-ghost-secondary'"
                );
                $add_comment_form = ob_get_clean();
            }

            $last_comment = null;
            if (count($comments_raw) > 0) {
                $lc = end($comments_raw);
                $last_comment = [
                    'user_name'     => getUserName($lc['users_id'], 0),
                    'date_relative' => Html::timestampToRelativeStr($lc['date_comment']),
                ];
            }

            $description      = $idea->fields['comment'];
            $description_long = strlen($description) > 10;

            $ideas[] = [
                'id'               => $id,
                'link'             => $idea->getLink(),
                'user_name'        => htmlspecialchars(
                    formatUserName($user->getID(), $user->fields['name'], $user->fields['realname'], $user->fields['firstname']),
                    ENT_QUOTES
                ),
                'avatar_style'     => $avatar_style,
                'user_initials'    => htmlspecialchars($user->getUserInitials(), ENT_QUOTES),
                'has_thumbnail'    => !empty($thumbnail_url),
                'date_relative'    => Html::timestampToRelativeStr($idea->fields['date_idea']),
                'state_color'      => self::getStateColor($idea->fields['state']),
                'state_name'       => self::getStateName($idea->fields['state']),
                'description'      => RichText::getEnhancedHtml($description),
                'description_long' => $description_long,
                'votes_count'      => $votes_count,
                'has_voted'        => (bool) $already_voted,
                'comments_count'   => count($comments_raw),
                'last_comment'     => $last_comment,
                'vote_form'        => $vote_form,
                'subscribe_form'   => $subscribe_form,
                'comments_modal'   => $comments_modal,
                'add_comment_form' => $add_comment_form,
                'label_read_desc'  => __s('Read description', 'ideabox'),
                'label_commented_by' => __s('Commented by', 'ideabox'),
            ];
        }

        TemplateRenderer::getInstance()->display('@ideabox/ideabox_list.html.twig', [
            'ideas' => $ideas,
        ]);
    }


    public static function showSearchForm()
    {

        echo "<div id='searchidea'>";
        echo "</div>";

        echo self::fuzzySearchForm('id-home-trigger-fuzzy');

    }


    /**
     * @param $name
     * @param $type
     * @return void
     */
    public static function fuzzySearchForm($name)
    {

        $title = __s("Start typing to find a idea", "ideabox");
        $strict_search = 1;


        $style = "style='display:none;margin-right: auto;margin-top: 20px;'";
        echo "<div tabindex='-1' id='fuzzysearch' $style>";

        $position = "";

        echo "<div class='modal-content' style='background-color: transparent!important;'>";
        echo "<div class='modal-body' style='padding: unset;background-color: transparent!important;" . $position . "width: 100%;'>";
        echo "<div class='input-group'>";

        echo "<input type='text' class='$name form-control' placeholder=\"" . $title . "\">";
        echo "<input type='hidden' name='fuzzy-strict' id='fuzzy-strict' value='" . $strict_search . "'/>";
        echo "<div class='input-group-prepend'>";
        echo "<span class='input-group-text input-group-text-search' style='padding: 10px;'><i class='ti ti-search'></i></span>";
        echo "</div>";
        echo "</div>";
        echo "<ul class='results list-group mb-2' style='background-color: transparent;'></ul>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo Html::scriptBlock("$(document).ready(function() {
                                        $('#fuzzysearch').show();
                                    });");
    }

    /**
     * Manage events from js/fuzzysearch.js
     *
     * @param string $action action to switch (should be actually 'getHtml' or 'getList')
     *
     * @return string
     * @since 9.2
     *
     */
    public static function fuzzySearch($action = '')
    {
        global $DB;

        $title = __s("Start typing to find a idea", "ideabox");
        $strict_search = 1;
        switch ($action) {
            case 'getModalHtml':
                $modal_header = __s('Search');
                $placeholder = $title;
                $html = <<<HTML
               <div class="modal" tabindex="-1" id="fuzzysearch">
                  <div class="modal-dialog">
                     <div class="modal-content">
                        <div class="modal-header">
                           <h5 class="modal-title">
                              <i class="ti ti-arrow-right me-2"></i>
                              {$modal_header}
                           </h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                           <input type="text" class="form-control" placeholder="{$placeholder}">
                           <input type="hidden" id="fuzzy-strict" value="{$strict_search}">
                           <ul class="results list-group mt-2"></ul>
                        </div>
                     </div>
                  </div>
               </div>

HTML;

                return $html;
                break;

            default:
                $fuzzy_entries = [];

                $criteria = [
                    'SELECT' => '*',
                    'FROM' => 'glpi_plugin_ideabox_ideaboxes',
                    'WHERE' => [
                        'is_deleted' => 0,
                    ],
                    'ORDERBY' => 'date_idea DESC',
                ];
                $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
                    'glpi_plugin_ideabox_ideaboxes'
                );

                $iterator = $DB->request($criteria);

                if (count($iterator) > 0) {
                    foreach ($iterator as $idea) {
                        $identity = __s('Idea');
                        $fuzzy_entries[] = [
                            'url' => PLUGIN_IDEABOX_WEBDIR . "/front/ideabox.php?id=" . $idea['id'],
                            'title' => $idea['name'],
                            'comment' => ($idea['comment'] != null) ? Html::resume_text(
                                RichText::getTextFromHtml($idea['comment']),
                                "200"
                            ) : "",
                            'icon' => 'ti ti-bulb',
                            'background' => '',
                            'order' => "2",
                            'target' => '',
                        ];
                    }
                }

                // return the entries to ajax call
                return json_encode($fuzzy_entries);
                break;
        }
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
                        `entities_id` int {$default_key_sign} NOT NULL DEFAULT '0',
                        `is_recursive` tinyint NOT NULL DEFAULT '0',
                        `name` varchar(255) collate utf8mb4_unicode_ci DEFAULT NULL,
                        `comment` text collate utf8mb4_unicode_ci,
                        `users_id` int {$default_key_sign} NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_users (id)',
                        `date_idea` timestamp DEFAULT NULL,
                        `state` int {$default_key_sign} NOT NULL DEFAULT '1',
                        `is_helpdesk_visible` int {$default_key_sign} NOT NULL DEFAULT '1',
                        `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
                        PRIMARY KEY  (`id`),
                        KEY `name` (`name`),
                        KEY `entities_id` (`entities_id`),
                        KEY `users_id` (`users_id`),
                        KEY `is_deleted` (`is_deleted`),
                        KEY `is_helpdesk_visible` (`is_helpdesk_visible`)
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

            // Notifications
            $options_notif        = ['itemtype' => self::class,
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
                            'itemtype' => self::class,
                            'event' => 'new',
                            'is_recursive' => 1,
                        ]
                    );
                    $options_notif        = ['itemtype' => self::class,
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
                            'itemtype' => self::class,
                            'event' => 'update',
                            'is_recursive' => 1,
                        ]
                    );
                    $options_notif        = ['itemtype' => self::class,
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
                            'itemtype' => self::class,
                            'event' => 'delete',
                            'is_recursive' => 1,
                        ]
                    );
                    $options_notif        = ['itemtype' => self::class,
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

            //DisplayPreferences Migration
            $classes = ['PluginIdeaboxIdeabox' => self::class];

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

        if (!$DB->fieldExists($table, "state")) {
            $migration->addField($table, "state", "int {$default_key_sign} NOT NULL DEFAULT '1'");
            $migration->migrationOneTable($table);
        }

        if (!$DB->fieldExists($table, "is_recursive")) {
            $migration->addField($table, "is_recursive", "tinyint NOT NULL DEFAULT '0'");
            $migration->migrationOneTable($table);
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

        if (class_exists('PluginDatainjectionModel')) {
            PluginDatainjectionModel::clean(['itemtype' => self::class]);
        }
    }
}
