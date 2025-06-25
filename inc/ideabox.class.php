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

use Glpi\Application\View\TemplateRenderer;

class PluginIdeaboxIdeabox extends CommonDBTM
{
    public $dohistory  = true;
    public static $rightname  = "plugin_ideabox";
    protected $usenotepad = true;

    const NEW = 1;
    const STUDY = 2;
    const IN_PROGRESS = 3;
    const CLOSED = 4;

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
        $temp = new PluginIdeaboxComment();
        $temp->deleteByCriteria(['plugin_ideabox_ideaboxes_id' => $this->fields['id']]);

        $temp = new PluginIdeaboxVote();
        $temp->deleteByCriteria(['plugin_ideabox_ideaboxes_id' => $this->fields['id']]);
    }

    /**
     * @return array
     */
//   static function getMenuContent() {
//
//      $menu                    = [];
//      $menu['title']           = self::getMenuName();
//      $menu['page']            = self::getSearchURL(false);
//      $menu['links']['search'] = self::getSearchURL(false);
//      if (self::canCreate()) {
//         $menu['links']['add'] = self::getFormURL(false);
//      }
//
//      $menu['icon']    = self::getIcon();
//
//      return $menu;
//   }


    public static function removeRightsFromSession()
    {
        if (isset($_SESSION['glpimenu']['tools']['types']['PluginIdeaboxIdeabox'])) {
            unset($_SESSION['glpimenu']['tools']['types']['PluginIdeaboxIdeabox']);
        }
        if (isset($_SESSION['glpimenu']['tools']['content']['pluginideaboxideabox'])) {
            unset($_SESSION['glpimenu']['tools']['content']['pluginideaboxideabox']);
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
        $this->addStandardTab('PluginIdeaboxComment', $ong, $options);
        if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $this->addStandardTab('Ticket', $ong, $options);
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
           'name' => self::getTypeName(2)
        ];

        $tab[] = [
           'id'            => '1',
           'table'         => $this->getTable(),
           'field'         => 'name',
           'name'          => __('Name'),
           'datatype'      => 'itemlink',
           'itemlink_type' => $this->getType(),
        ];

        $tab[] = [
           'id'            => '7',
           'table'         => $this->getTable(),
           'field'         => 'date_idea',
           'name'          => __('Date of submission', 'ideabox'),
           'datatype'      => 'datetime',
           'massiveaction' => false,
        ];

        $tab[] = [
           'id'       => '10',
           'table'    => 'glpi_users',
           'field'    => 'name',
           'name'     => __('Author'),
           'datatype' => 'dropdown',
           'right'    => 'all',
        ];

        $tab[] = [
           'id'       => '8',
           'table'    => $this->getTable(),
           'field'    => 'comment',
           'name'     => __('Description', 'ideabox'),
           'datatype' => 'text',
        ];

        $tab[] = [
           'id'       => '9',
           'table'    => $this->getTable(),
           'field'    => 'is_helpdesk_visible',
           'name'     => __('Associable to a ticket'),
           'datatype' => 'bool',
        ];

        $tab[] = [
           'id'       => '30',
           'table'    => $this->getTable(),
           'field'    => 'id',
           'name'     => __('ID'),
           'datatype' => 'number',
        ];

        $tab[] = [
           'id'       => '80',
           'table'    => 'glpi_entities',
           'field'    => 'completename',
           'name'     => __('Entity'),
           'datatype' => 'dropdown',
        ];

        return $tab;
    }


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
            Session::addMessageAfterRedirect(__("The name is mandatory", "ideabox"), false, ERROR);
            return false;
        }

        if (empty($input['comment'])) {
            Session::addMessageAfterRedirect(__("The description is mandatory", "ideabox"), false, ERROR);
            return false;
        }

        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        if (Session::getCurrentInterface() != 'central'
            && $input['users_id'] != Session::getLoginUserID()) {
            Session::addMessageAfterRedirect(__("Only original author can modify it", "ideabox"), false, ERROR);
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
     * @return \translated
     */
    public static function getStateName($state)
    {
        switch ($state) {

            case self::STUDY:
                return __('In study', 'ideabox');
            case self::IN_PROGRESS:
                return __('In progress', 'ideabox');
            case self::CLOSED:
                return __('Closed', 'ideabox');
            default:
                return __('New', 'ideabox');
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
            'SELECT' => 'id',
            'FROM' => 'glpi_plugin_ideabox_ideaboxes',
            'WHERE' => [
                'is_deleted' => 0
            ],
            'ORDERBY' => 'date_idea DESC',
        ];

        if (isset($params['id'])) {
            $criteria['WHERE'] = $criteria['WHERE'] + ['id' => $params['id']];
        }

        $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
                'glpi_plugin_ideabox_ideaboxes'
            );

        $iterator = $DB->request($criteria);

        if (count($iterator) > 0) {
            echo "<div class='topiclist-topics' style='display: flex;flex-wrap: wrap;'>";
            foreach ($iterator as $array) {
                $idea = new self();
                $idea->getFromDB($array['id']);

                $comments = [];

                $criteriac = [
                    'SELECT' => '*',
                    'FROM' => 'glpi_plugin_ideabox_comments',
                    'WHERE' => [
                        'plugin_ideabox_ideaboxes_id' => $idea->getID()
                    ]
                ];
                $iteratorc = $DB->request($criteriac);

                if (count($iteratorc) > 0) {
                    foreach ($iteratorc as $array2) {
                        $comments[$array2['id']]['users_id'] = $array2['users_id'];
                        $comments[$array2['id']]['name'] = $array2['name'];
                        $comments[$array2['id']]['comment'] = $array2['comment'];
                        $comments[$array2['id']]['date_comment'] = $array2['date_comment'];
                    }
                }

                $votes = [];

                $criteriav = [
                    'SELECT' => '*',
                    'FROM' => 'glpi_plugin_ideabox_votes',
                    'WHERE' => [
                        'plugin_ideabox_ideaboxes_id' => $idea->getID()
                    ]
                ];
                $iteratorv = $DB->request($criteriav);

                if (count($iteratorv) > 0) {
                    foreach ($iteratorv as $array3) {
                        $votes[$array3['id']]['users_id'] = $array3['users_id'];
                        $votes[$array3['id']]['date_comment'] = $array3['date_vote'];
                    }
                }
                $id = $idea->getID();
                echo "<div id='anchor$id'  class='topic-item topic-item-medium-list' style='flex: 1 0 45%;position: relative;'>";

                echo "<div class='topic-avatar'>";
                $user = new User();
                $user->getFromDB($idea->fields['users_id']);
                $thumbnail_url = User::getThumbnailURLForPicture($user->fields['picture']);
                $style = !empty($thumbnail_url) ? "background-image: url('$thumbnail_url')" : ("background-color: " . $user->getUserInitialsBgColor(
                    ));
                $user_name = formatUserName(
                    $user->getID(),
                    $user->fields['name'],
                    $user->fields['realname'],
                    $user->fields['firstname']
                );
                echo '<span class="avatar avatar-md rounded" style="' . $style . '" title="' . $user_name . '">';
                if (empty($thumbnail_url)) {
                    echo $user->getUserInitials();
                }
                echo "</span>";
                echo "</div>";

                echo '<div class="topic-votes pull-right">';
                echo '<span title="" class="topic-label topic-label-success" >';
                echo '+' . count($votes);
                echo "</span>";
                echo "</div>";

                echo "<div class='topic-status'>";
                $color = self::getStateColor($idea->fields['state']);
                echo "<span class='topic-label topic-label-sm' style='background-color:".$color."'>";
                echo self::getStateName($idea->fields['state']);
                echo "</div>";


                echo "<h3 class='topic-header'>";
                echo $idea->getLink();
                echo "</h3>";

                echo "<div class='topic-details'>";
                echo '<i class="ti ti-bulb icon-source"></i>';

                echo getUserName($idea->fields['users_id'], 0, true);
                echo ' - <span class="date-created">';
                echo Html::timestampToRelativeStr($idea->fields['date_idea']);

                if (count($comments) > 0) {
                    $last_comment = end($comments);

                    echo "</span>";
                    echo ' - <span class="topic-updated-info">';

                    echo __('Commented by', 'ideabox');
                    echo "&nbsp;" . getUserName($last_comment['users_id'], 0, true);
                    echo ' - <span class="date-updated">';

                    echo Html::timestampToRelativeStr($last_comment['date_comment']);
                    echo "</span>";
                    echo ' - <span class="topic-comment-count">';

                    $id = $idea->getID();
                    echo "<button class='submit btn btn-default mb-2' data-bs-toggle='modal' data-bs-target='#seecomments$id'>"
                        . "<i class='ti ti-message'></i><span>" . count($comments). "</span></button>";

                    echo Ajax::createIframeModalWindow(
                        'seecomments'.$id,
                        PLUGIN_IDEABOX_WEBDIR . '/front/comment.php?plugin_ideabox_ideaboxes_id=' . $idea->getID(),
                        ['title'         => __("See comments", 'ideabox'),
                            'display'       => false,
//                            'width'         => 550,
//                            'height'        => 850,
                            'reloadonclose' => true]
                    );

                    echo "</span>";
                } else {
                    echo "&nbsp;";
                    $target = $idea->getFormURL();
                    $target .= "?forcetab=PluginIdeaboxComment$1&id=".$idea->getID();
                    Html::showSimpleForm(
                        $target,
                        'addcomment',
                        '',
                        ['plugin_ideabox_ideaboxes_id' => $idea->getID()],
                        'ti-message-plus',
                        "class='btn btn-default'"
                    );
                }

                echo "</div>";

                echo '<div class="topic-text ue-content">';

                $description = $idea->fields['comment'];

                if (strlen($idea->fields['comment']) > 10) {
                    echo "<a href=\"#anchor$id\" onclick=\"$(this).hide();$('#$id').show();\">" . __(
                            'Read description',
                            'ideabox'
                        ) . "</a>";
                    echo '<div style="display:none;padding-bottom: 10px;" id="' . $id . '">' . Glpi\RichText\RichText::getEnhancedHtml(
                            $description
                        ) . '</div>';
                } else {
                    echo Glpi\RichText\RichText::getEnhancedHtml($description);
                }

                echo '</div>';

                echo '<div class="actions-bar">';
                echo '<div style="bottom: 5px;position: absolute">';
                echo '<span class="vote-text hidden-xs">';
                echo __('Add your vote', 'ideabox');
                echo '&nbsp;</span>';
                $already_voted = 0;
                $target = $idea->getFormURL();
                $vote = new PluginIdeaboxVote();
                if ($vote->getFromDBByCrit(['users_id' => Session::getLoginUserID(),'plugin_ideabox_ideaboxes_id' =>  $idea->getID()])) {
                    $already_voted = 1;
                }
                if ($already_voted == 0) {

                    Html::showSimpleForm(
                        $target,
                        'vote',
                        count($votes),
                        ['id' => $idea->getID()],
                        'ti-thumb-up',
                        "class='btn btn-default'"
                    );
                } else {
                    Html::showSimpleForm(
                        $target,
                        'cancelvote',
                        _x('button', 'Cancel', 'ideabox'),
                        ['id' => $idea->getID()],
                        'ti-circle-x',
                        "class='btn btn-default'"
                    );
                }
                echo "</div>";


                $target = "";
                Html::showSimpleForm(
                    $target,
                    'suscribe',
                    _x('button', 'Suscribe', 'ideabox'),
                    ['id' => $idea->getID()],
                    'ti-mail',
                    "style='float: right;position: absolute;bottom: 5px;right: 5px;color: #CCC;' class='btn btn-default'"
                );

                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        }
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

        $title = __("Start typing to find a idea", "ideabox");
        $strict_search = 1;


        $style = "style='display:none;margin-right: auto;margin-top: 20px;'";
        echo "<div tabindex='-1' id='fuzzysearch' $style>";

        $position = "";

        echo "<div class='modal-content' style='background-color: transparent!important;'>";
        echo "<div class='modal-body' style='padding: unset;background-color: transparent!important;".$position."width: 100%;'>";
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

        $title = __("Start typing to find a idea", "ideabox");
        $strict_search = 1;
        switch ($action) {
            case 'getModalHtml':
                $modal_header = __('Search');
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
                        'is_deleted' => 0
                    ],
                    'ORDERBY' => 'date_idea DESC',
                ];
                $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
                        'glpi_plugin_ideabox_ideaboxes'
                    );

                $iterator = $DB->request($criteria);

                if (count($iterator) > 0) {
                    foreach ($iterator as $idea) {
                        $identity = __('Idea');
                        $fuzzy_entries[] = [
                            'url' => PLUGIN_IDEABOX_WEBDIR . "/front/ideabox.php?id=" . $idea['id'],
                            'title' => $idea['name'],
                            'comment' => ($idea['comment'] != null) ? Html::resume_text(
                                Glpi\RichText\RichText::getTextFromHtml($idea['comment']),
                                "200"
                            ) : "",
                            'icon' => 'ti ti-bulb',
                            'background' => '',
                            'order' => "2",
                            'target' => ''
                        ];
                    }
                }

                // return the entries to ajax call
                return json_encode($fuzzy_entries);
                break;
        }
    }
}
