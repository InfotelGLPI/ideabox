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

class PluginIdeaboxComment extends CommonDBChild
{
    public static $rightname = "plugin_ideabox";

    public static $itemtype = 'PluginIdeaboxIdeabox';
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
            return self::getTypeName(2);
        }
        return '';
    }

    public static function countForIdea(PluginIdeaboxIdeabox $item)
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
        if ($item->getType() == 'PluginIdeaboxIdeabox') {
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

    public function post_addItem()
    {
        global $CFG_GLPI;

        $idea = new PluginIdeaboxIdeabox();
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

        $idea = new PluginIdeaboxIdeabox();
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
            Session::addMessageAfterRedirect(__("Only original author can modify it", "ideabox"), false, ERROR);
            return false;
        }

        $idea = new PluginIdeaboxIdeabox();
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

        $criteriac = [
            'SELECT' => '*',
            'FROM' => 'glpi_plugin_ideabox_comments',
            'WHERE' => [
                'plugin_ideabox_ideaboxes_id' => $ID
            ],
            'ORDERBY' => 'date_comment DESC'
        ];
        $iteratorc = $DB->request($criteriac);

        if (count($iteratorc) > 0) {
            echo '<div class="module module-comments">';
            echo '<div class="module-body">';
            echo '<ul class="nav nav-pills" style="margin-bottom: 10px;">';
            echo '<li>';
            echo '<div class="text-21">';
            echo _n('Comment', 'Comments', count($iteratorc), 'ideabox').'&nbsp;<span class="badge">'.count($iteratorc).'</span>';
            echo '</div>';
            echo '</li>';
            echo '</ul>';
            echo '<div class="comments-list" data-comments-order="up" data-topic-id="2049">';

            foreach ($iteratorc as $array2) {
                echo '<div class="comment-item co0 ">';
                echo '<div class="topic-avatar">';
                $user = new User();
                $user->getFromDB($array2['users_id']);
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

                echo '<div class="comment-details">';
                echo '<i class="fa-fw ti ti-message"></i>&nbsp;';

                echo getUserName($array2['users_id'], 0, true);
                echo ' - <span class="date-created">';
                echo Html::timestampToRelativeStr($array2['date_comment']);
                echo "</span>";
                echo "</div>";

                echo '<div class="comment-text ue-content">';
                echo Glpi\RichText\RichText::getEnhancedHtml($array2['comment']);
                echo "</div>";

                if ($fromidea == false) {
                    $idea = new PluginIdeaboxIdeabox();
                    $target = $idea->getFormURL();
                    $target .= "?forcetab=PluginIdeaboxComment$1&id=".$ID;
                    Html::showSimpleForm(
                        $target,
                        'addcomment',
                        _sx('button', 'Post a comment', 'ideabox'),
                        ['plugin_ideabox_ideaboxes_id' => $ID],
                        '',
                        "class='btn btn-default'"
                    );
                }

                if ($array2['users_id']== Session::getLoginUserID()) {
                    echo "&nbsp;";
                    $self = new self();
                    $target = $self->getFormURL();
                    Html::showSimpleForm(
                        $target,
                        'purge',
                        _sx('button', 'Delete', 'ideabox'),
                        ['id' => $array2['id']],
                        '',
                        "class='btn btn-danger'"
                    );
                }


                echo "</div>";
            }
            echo "</div>";
        }

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
           'field'         => 'date_comment',
           'name'          => __('Date of comment', 'ideabox'),
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
           'id'       => '30',
           'table'    => $this->getTable(),
           'field'    => 'id',
           'name'     => __('ID'),
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

    public function showComments(PluginIdeaboxIdeabox $ideabox)
    {
        global $DB, $CFG_GLPI;

        $instID = $ideabox->fields['id'];

        if (!$ideabox->can($instID, READ)) {
            return false;
        }

        $rand    = mt_rand();
        $canedit = $ideabox->can($instID, UPDATE);

        $query  = "SELECT `glpi_plugin_ideabox_comments`.`name` AS name,
                        `glpi_plugin_ideabox_comments`.`id`,
                        `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id`,
                        `glpi_plugin_ideabox_comments`.`date_comment`,
                        `glpi_plugin_ideabox_comments`.`comment`,
                        `glpi_plugin_ideabox_comments`.`users_id` AS users_id
               FROM `glpi_plugin_ideabox_comments` ";
        $query  .= " LEFT JOIN `glpi_plugin_ideabox_ideaboxes`
      ON (`glpi_plugin_ideabox_ideaboxes`.`id` = `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id`)";
        $query  .= " WHERE `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id` = '$instID'
          ORDER BY `glpi_plugin_ideabox_comments`.`name`";
        $result = $DB->doQuery($query);
        $number = $DB->numrows($result);

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

            echo "<th>" . __('Name') . "</th>";
            echo "<th>" . __('Author') . "</th>";
            echo "<th>" . __('Date') . "</th>";
            echo "<th>" . __('Description', 'ideabox') . "</th>";

            echo "</tr>";

            Session::initNavigateListItems($this->getType(), PluginIdeaboxIdeabox::getTypeName(2) . " = " . $ideabox->fields["name"]);
            $i       = 0;
            $row_num = 1;

            while ($data = $DB->fetchArray($result)) {
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
                echo $data["name"];
                if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                    echo " (" . $data["id"] . ")";
                }
                echo "</a></td>";

                echo "<td class='left'>" . getusername($data["users_id"]) . "</td>";
                echo "<td class='left'>" . Html::convdatetime($data["date_comment"]) . "</td>";
                echo "<td class='left'>" . Glpi\RichText\RichText::getTextFromHtml($data["comment"]) . "</td>";
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
}
