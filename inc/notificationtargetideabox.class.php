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

// Class NotificationTargetIdeabox
class PluginIdeaboxNotificationTargetIdeabox extends NotificationTarget
{
    const IDEABOX_USER         = 4900;
    const IDEABOX_COMMENT_USER = 4901;

    public function getEvents() {
        return ['new'           => __('A new idea has been submitted', 'ideabox'),
                'update'        => __('An idea has been modified', 'ideabox'),
                'delete'        => __('An idea has been deleted', 'ideabox'),
                'newcomment'    => __('A comment has been added', 'ideabox'),
                'updatecomment' => __('A comment has been modified', 'ideabox'),
                'deletecomment' => __('A comment has been deleted', 'ideabox')];
    }

    /**
     * Get additionnals targets for Tickets
     */
    public function addAdditionalTargets($event = '') {
        $this->addTarget(PluginIdeaboxNotificationTargetIdeabox::IDEABOX_USER, __('Author'));
        $this->addTarget(PluginIdeaboxNotificationTargetIdeabox::IDEABOX_COMMENT_USER, __('Comment author', 'ideabox'));
    }

    public function addSpecificTargets($data, $options) {
        //Look for all targets whose type is Notification::ITEM_USER
        switch ($data['items_id']) {
            case PluginIdeaboxNotificationTargetIdeabox::IDEABOX_USER:
                $this->getUserAddress();
                break;
            case PluginIdeaboxNotificationTargetIdeabox::IDEABOX_COMMENT_USER:
                $this->getUserCommentAddress();
                break;
        }
    }

    //Get recipient
    public function getUserAddress() {
        return $this->getUserByField("users_id");
    }

    public function getUserCommentAddress() {
        global $DB;

        $query = "SELECT DISTINCT `glpi_users`.`id` AS id
                FROM `glpi_plugin_ideabox_comments`
                LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_plugin_ideabox_comments`.`users_id`)
                WHERE `glpi_plugin_ideabox_comments`.`plugin_ideabox_ideaboxes_id` = '" . $this->obj->fields["id"] . "'";

        foreach ($DB->request($query) as $data) {
            $data['email'] = UserEmail::getDefaultForUser($data['id']);
            $this->addToAddressesList($data);
        }
    }

    public function addDataForTemplate($event, $options = []) {
        global $CFG_GLPI;

        $events = $this->getAllEvents();
        $ideabox = $this->obj;

        if (!isset($options['ideabox'])) {
            $options['ideabox'] = [];
            if (!$ideabox->isNewItem()) {
                $options['ideabox'][] = $ideabox->fields;// Compatibility with old behaviour
            }
        }
        
        $this->data['##lang.ideabox.title##'] = $events[$event];

        $this->data['##lang.ideabox.entity##'] = __('Entity');
        $this->data['##ideabox.entity##']      =
            Dropdown::getDropdownName(
                'glpi_entities',
                $ideabox->fields['entities_id']
            );
        $this->data['##ideabox.id##']          = sprintf("%07d", $ideabox->fields['id']);

        $this->data['##lang.ideabox.name##'] = __('Title');
        $this->data['##ideabox.name##']      = $ideabox->fields['name'];

        $this->data['##lang.ideabox.comment##'] = __('Description');
        $comment                                 = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $ideabox->fields['comment']));
        $this->data['##ideabox.comment##']      = nl2br($comment);

        $this->data['##lang.ideabox.url##'] = "URL";
        $this->data['##ideabox.url##']      = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=plugin_ideabox_" .
                                                         $ideabox->fields['id']);

        //old values infos
        if (isset($this->target_object->oldvalues)
            && !empty($this->target_object->oldvalues) && $event == 'update') {
            $this->data['##lang.update.title##'] = __('Modified fields', 'ideabox');

            $tmp = [];

            if (isset($this->target_object->oldvalues['name'])
                && !empty($this->target_object->oldvalues['name'])) {
                $tmp['##update.name##'] = $this->target_object->oldvalues['name'];
            }
            if (isset($this->target_object->oldvalues['comment'])
                && !empty($this->target_object->oldvalues['comment'])) {
                $tmp['##update.comment##'] = nl2br($this->target_object->oldvalues['comment']);
            }

            $this->data['updates'][] = $tmp;
        }

        //comment infos
        $restrict = "`plugin_ideabox_ideaboxes_id`='" . $ideabox->fields['id'] . "'";

        if (isset($options['comment_id']) && $options['comment_id']) {
            $restrict .= " AND `glpi_plugin_ideabox_comments`.`id` = '" . $options['comment_id'] . "'";
        }

        $comments = getAllDataFromTable('glpi_plugin_ideabox_comments', ['ORDER' => 'date_comment DESC']);

        $this->data['##lang.comment.title##'] = _n('Associated comment', 'Associated comments', 2, 'ideabox');

        $this->data['##lang.comment.name##']        = __('Name');
        $this->data['##lang.comment.author##']      = __('Comment author', 'ideabox');
        $this->data['##lang.comment.datecomment##'] = __('Date');
        $this->data['##lang.comment.comment##']     = __('Content');

        foreach ($comments as $comment) {
            $tmp = [];

            $tmp['##comment.name##']        = $comment['name'];
            $tmp['##comment.author##']      = getUserName($comment['users_id']);
            $tmp['##comment.datecomment##'] = Html::convDateTime($comment['date_comment']);
            $tmp['##comment.comment##']     = nl2br($comment['comment']);

            $this->data['comments'][] = $tmp;
        }
    }

    public function getTags() {
        $tags = ['ideabox.name'        => __('Title'),
                 'ideabox.comment'     => __('Description'),
                 'comment.name'        => __('Name'),
                 'comment.author'      => __('Comment author', 'ideabox'),
                 'comment.datecomment' => __('Date'),
                 'comment.comment'     => __('Content')];
        foreach ($tags as $tag => $label) {
            $this->addTagToList(['tag'   => $tag, 'label' => $label,
                                 'value' => true]);
        }

        $this->addTagToList(['tag'     => 'ideabox',
                             'label'   => __('An addition/modification/deletion of ideas', 'ideabox'),
                             'value'   => false,
                             'foreach' => true,
                             'events'  => ['new', 'update', 'delete']]);
        $this->addTagToList(['tag'     => 'comments',
                             'label'   => __('An addition/modification/deletion of comments', 'ideabox'),
                             'value'   => false,
                             'foreach' => true,
                             'events'  => ['newcomment', 'updatecomment', 'deletecomment']]);

        asort($this->tag_descriptions);
    }
}
