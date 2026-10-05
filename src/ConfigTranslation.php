<?php

/**
 * -------------------------------------------------------------------------
 * ideabox plugin for GLPI
 * Copyright (C) 2025-2026 by the ideabox Development Team.
 *
 * https://github.com/InfotelGLPI/ideabox
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ideabox.
 *
 * ideabox is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * ideabox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ideabox. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Ideabox;

use CommonDBChild;
use CommonGLPI;
use DBConnection;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Glpi\RichText\RichText;
use Migration;
use Session;

/**
 * ConfigTranslation Class
 *
 **/
class ConfigTranslation extends CommonDBChild
{
    public static string $itemtype  = 'itemtype';
    public static string $items_id  = 'items_id';
    public bool $dohistory = true;

    public static string $rightname = 'plugin_ideabox';

    // The setup is an administration screen: front/config.form.php requires the
    // "config" UPDATE right, and the core generic endpoints (tabs, massive actions)
    // only evaluate these static checks, so they must enforce the same right.
    public static function canView(): bool
    {
        return Session::haveRight(\Config::$rightname, UPDATE);
    }

    public static function canCreate(): bool
    {
        return Session::haveRight(\Config::$rightname, UPDATE);
    }

    public static function canUpdate(): bool
    {
        return Session::haveRight(\Config::$rightname, UPDATE);
    }

    public static function canDelete(): bool
    {
        return Session::haveRight(\Config::$rightname, UPDATE);
    }

    public static function canPurge(): bool
    {
        return Session::haveRight(\Config::$rightname, UPDATE);
    }


    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @param integer $nb Number of items
     *
     * @return string
     **/
    public static function getTypeName($nb = 0)
    {
        return _n('Translation', 'Translations', $nb);
    }


    /**
     * Mass-assignment guard, symmetric with prepareInputForUpdate(): restrict the
     * accepted columns to the translation schema so a forged POST cannot write
     * arbitrary columns. Mirrors the whitelist pattern used by Ideabox/Comment.
     *
     * @param array $input
     *
     * @return array|false
     */
    public function prepareInputForAdd($input)
    {
        $allowed = ['id', 'itemtype', 'items_id', 'language', 'field', 'value'];
        $input   = array_intersect_key($input, array_flip($allowed));
        // Only the plugin setup is translated
        if (($input['itemtype'] ?? null) !== Config::class) {
            return false;
        }

        return parent::prepareInputForAdd($input);
    }


    /**
     * Mass-assignment guard, symmetric with prepareInputForAdd().
     *
     * @param array $input
     *
     * @return array|false
     */
    public function prepareInputForUpdate($input)
    {
        $allowed = ['id', 'itemtype', 'items_id', 'language', 'field', 'value'];
        $input   = array_intersect_key($input, array_flip($allowed));
        if (isset($input['itemtype']) && $input['itemtype'] !== Config::class) {
            return false;
        }

        return parent::prepareInputForUpdate($input);
    }


    /**
     * Get the standard massive actions which are forbidden
     *
     * @return array an array of massive actions
     **@since version 0.84
     *
     * This should be overloaded in Class
     *
     */
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * @param CommonGLPI $item
     * @param int         $withtemplate
     *
     * @return array|string
     * @see CommonGLPI::getTabNameForItem()
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        $nb = self::getNumberOfTranslationsForItem($item);
        return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
    }


    public static function getIcon()
    {
        return "ti ti-language";
    }

    /**
     * @param $item            CommonGLPI object
     * @param $tabnum (default 1)
     * @param $withtemplate (default 0)
     **
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if (self::canBeTranslated($item)) {
            self::showTranslations($item);
        }
        return true;
    }


    /**
     * Display all translated field for a Config
     *
     * @param Config $item a Config item
     *
     * @return true
     **/
    public static function showTranslations(Config $item)
    {
        $canedit = $item->can($item->getID(), UPDATE);
        $rand    = mt_rand();

        $obj   = new self();
        $found = $obj->find(['items_id' => $item->getID()], "language ASC");

        $entries = [];
        foreach ($found as $data) {
            $search_option = $item->getSearchOptionByField('field', $data['field']);
            $entries[] = [
                'itemtype' => self::class,
                'id' => $data['id'],
                // Clicking a row opens its edition form (public/scripts/config-translation.js)
                'row_class' => $canedit ? 'cursor-pointer' : '',
                'language' => Dropdown::getLanguageName($data['language']),
                'field' => $search_option['name'] ?? $data['field'],
                // Rich text sanitized by the core, rendered as a raw_html cell like DropdownTranslation does
                'value' => '<div class="rich_text_container">' . RichText::getSafeHtml($data['value'] ?? '') . '</div>',
            ];
        }

        TemplateRenderer::getInstance()->display('@ideabox/configtranslation_list.html.twig', [
            'canedit' => $canedit,
            'rand' => $rand,
            'type' => self::class,
            'view_url' => PLUGIN_IDEABOX_WEBDIR . '/ajax/viewsubitem.php',
            'parenttype' => $item::class,
            'items_id' => $item->getID(),
            'entries' => $entries,
            'container' => 'mass' . static::class . $rand,
            'script_url' => PLUGIN_IDEABOX_WEBDIR . '/scripts/config-translation.js',
        ]);

        return true;
    }


    public function showForm($ID = -1, array $options = [])
    {
        if (!isset($options['parent'])) {
            // parent is mandatory
            trigger_error('Parent item must be defined in `$options["parent"]`.', E_USER_WARNING);
            return false;
        }
        $item = $options['parent'];

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            $options['itemtype'] = get_class($item);
            $options['items_id'] = $item->getID();

            $this->check(-1, CREATE, $options);
        }

        TemplateRenderer::getInstance()->display('@ideabox/ideabox_translation.html.twig', [
            'parent_item' => $item,
            'item' => $this,
            'search_option' => !$item->isNewItem() ? $item->getSearchOptionByField('field', $this->fields['field']) : [],
            'matching_field' => [],//$item->getAdditionalField($this->fields['field'])
            'no_header' => true,
        ]);
        return true;
    }



    /**
     * Check if an item can be translated
     * It be translated if translation if globally on and item is an instance of CommonDropdown
     * or CommonTreeDropdown and if translation is enabled for this class
     *
     * @param CommonGLPI $item
     *
     * @return true if item can be translated, false otherwise
     */
    public static function canBeTranslated(CommonGLPI $item)
    {

        return ($item instanceof Config);
    }


    /**
     * Return the number of translations for an item
     *
     * @param Config item
     *
     * @return int number of translations for this item
     */
    public static function getNumberOfTranslationsForItem(Config $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(
            $dbu->getTableForItemType(__CLASS__),
            ["items_id" => $item->getID()],
        );
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
                        `items_id` int {$default_key_sign} NOT NULL DEFAULT '0',
                        `itemtype` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        `language` varchar(5) COLLATE utf8mb4_unicode_ci   DEFAULT NULL,
                        `field`    varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        `value`    text COLLATE utf8mb4_unicode_ci         DEFAULT NULL,
                        PRIMARY KEY (`id`)
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
