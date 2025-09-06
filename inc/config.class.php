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

/**
 * Class PluginIdeaboxConfig
 */
class PluginIdeaboxConfig extends CommonDBTM
{
    public static $rightname = 'plugin_ideabox';
    public $can_be_translated = true;

    /**
     * functions mandatory
     * getTypeName(), canCreate(), canView()
     * */
    public static function getTypeName($nb = 0)
    {
        return __('Setup');
    }


    /**
     * PluginIdeaboxConfig constructor.
     */
    public function __construct()
    {
        global $DB;

        if ($DB->tableExists($this->getTable())) {
            $this->getFromDB(1);
        }
    }

    /**
     * @param array $options
     *
     * @return array
     * @see CommonGLPI::defineTabs()
     */
    public function defineTabs($options = [])
    {
        $ong = [];
        //      $this->addDefaultFormTab($ong);
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('PluginIdeaboxConfigTranslation', $ong, $options);
        return $ong;
    }


    /**
     * @param CommonGLPI $item
     * @param int        $withtemplate
     *
     * @return string
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if (!$withtemplate) {
            $ong[1] =  self::createTabEntry(__('Setup'));
            return $ong;
        }
        return '';
    }

    static function getIcon() {
        return "ti ti-settings";
    }

    /**
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showConfigForm($item->getID());
                    break;
            }
        }
        return true;
    }

    /**
     * Get the Search options for the given Type
     *
     * This should be overloaded in Class
     *
     * @return array an *indexed* array of search options
     *
     * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
     **/
    function rawSearchOptions() {

        $tab = [];

        $tab[] = [
            'id'   => 'common',
            'name' => self::getTypeName(2)
        ];

        $tab[] = [
            'id'         => '1',
            'table'      => $this->getTable(),
            'field'      => 'title',
            'name'       => __('Title'),
            'searchtype' => 'equals',
            'datatype'   => 'text'
        ];

        $tab[] = [
            'id'         => '2',
            'table'      => $this->getTable(),
            'field'      => 'comment',
            'name'       => __('Comments'),
            'searchtype' => 'equals',
            'datatype'   => 'text'
        ];
        return $tab;
    }

    /**
     * @return bool
     */
    public function showConfigForm()
    {
        if (!$this->canView()) {
            return false;
        }
        if (!$this->canCreate()) {
            return false;
        }

        $canedit = true;

        if ($canedit) {
            $ID = 1;
            $this->getFromDB($ID);
            echo "<form name='form' method='post' action='" . $this->getFormURL() . "'>";

            echo Html::hidden('id', ['value' => $ID]);

            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='4'>".self::getTypeName()."</th></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo __('Title');
            echo "</td>";
            echo "<td>";
            echo Html::input('title', ['value' => $this->fields['title'], 'size' => 40]);
            echo "</td>";

            echo "<td>";
            echo __('Comments');
            echo "</td>";
            echo "<td>";
            echo Html::input('comment', ['value' => $this->fields['comment'], 'size' => 40]);
            echo "</td>";
            echo "</tr>";


            echo "<tr>";
            echo "<td class='tab_bg_2 center' colspan='4'>";
            echo Html::submit(_sx('button', 'Update'), ['name' => 'update_setup', 'class' => 'btn btn-primary']);
            echo "</td>";
            echo "</tr>";
            echo "</table></div>";
            Html::closeForm();
        }
    }

    /**
     * Returns the translation of the field
     *
     * @return type
     * @global type $DB
     *
     */
    public static function displayField($item, $field, $withclean = true)
    {
        global $DB;

        // Make new database object and fill variables
        $iterator = $DB->request([
                                     'FROM'  => 'glpi_plugin_ideabox_configtranslations',
                                     'WHERE' => [
                                         'itemtype' => 'PluginIdeaboxConfig',
                                         'items_id' => '1',
                                         'field'    => $field,
                                         'language' => $_SESSION['glpilanguage']
                                     ]]);

        if (count($iterator)) {
            foreach ($iterator as $data) {
                //            if ($withclean == true) {
                return Glpi\RichText\RichText::getTextFromHtml($data['value']);
                //            } else {
                //               return $data['value'];
                //            }
            }
        }
        if ($withclean == true && isset($item->fields[$field])) {
            return Glpi\RichText\RichText::getTextFromHtml($item->fields[$field]);
        } elseif (isset($item->fields[$field])) {
            return $item->fields[$field];
        }
    }
}
