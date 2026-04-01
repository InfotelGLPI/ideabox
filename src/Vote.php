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

namespace GlpiPlugin\Ideabox;
use CommonDBChild;
use DBConnection;
use Migration;
use Session;

class Vote extends CommonDBChild
{
    public static $rightname = "plugin_ideabox";

    public static $itemtype = Ideabox::class;
    public static $items_id = 'plugin_ideabox_ideaboxes_id';

    public static function getTypeName($nb = 0)
    {
        return _n('Vote', 'Votes', $nb, 'ideabox');
    }

    /**
     * @return string
     */
    public static function getIcon()
    {
        return "ti ti-thump-up";
    }

    public function prepareInputForAdd($input)
    {
        if ($this->getFromDBByCrit(['users_id' => Session::getLoginUserID(),
            'plugin_ideabox_ideaboxes_id' =>  $input['plugin_ideabox_ideaboxes_id']])) {
            return false;
        }

        return $input;
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
                        `date_vote` timestamp DEFAULT NULL,
                        `plugin_ideabox_ideaboxes_id` int {$default_key_sign} NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_ideabox_ideaboxes (id)',
                        `users_id` int {$default_key_sign} NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_users (id)',
                        PRIMARY KEY  (`id`),
                        KEY `plugin_ideabox_ideaboxes_id` (`plugin_ideabox_ideaboxes_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

            $DB->doQuery($query);

        }
    }
    public static function uninstall()
    {
        global $DB;

        $DB->dropTable(self::getTable(), true);
    }
}
