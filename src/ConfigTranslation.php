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
use CommonGLPI;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Glpi\RichText\RichText;
use Html;
use Session;

/**
 * ConfigTranslation Class
 *
 **/
class ConfigTranslation extends CommonDBChild
{
    public static $itemtype  = 'itemtype';
    public static $items_id  = 'items_id';
    public $dohistory = true;

    public static $rightname = 'plugin_ideabox';


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
        if ($canedit) {
            $twig_params = [
                'item' => $item,
                'rand' => $rand,
                'button_msg' => __('Add a new translation'),
            ];
            // language=Twig
            echo TemplateRenderer::getInstance()->renderFromStringTemplate(<<<TWIG
                <div class="text-center">
                    <button class="btn btn-primary" onclick="showTranslation{{ item.getID() ~ rand }}(-1)">{{ button_msg }}</button>
                </div>
                <div id="viewtranslation{{ item.getID() ~ rand }}" class="mb-3"></div>
                <script>
                    function showTranslation{{ item.getID() ~ rand }}(translations_id) {
                        $.ajax({
                            url: CFG_GLPI.root_doc + '/plugins/ideabox/ajax/viewsubitem.php',
                            method: 'POST',
                            data: {
                                type: 'GlpiPlugin\\\\Ideabox\\\\ConfigTranslation',
                                parenttype: '{{ item.getType()|e('js') }}',
                                items_id: {{ item.getID() }},
                                id: translations_id
                            },
                            success: (data) => {
                                $('#viewtranslation{{ item.getID() ~ rand }}').html(data);
                            }
                        });
                    }
                    $(() => {
                        $('#translationlist{{ rand }} tbody tr').on('click', function() {
                            showTranslation{{ item.getID() ~ rand }}($(this).attr('data-id'));
                        });
                    });
                </script>
TWIG, $twig_params);
        }

        $obj   = new self();
        $found = $obj->find(['items_id' => $item->getID()], "language ASC");

        $entries = [];
        foreach ($found as $data) {
            $entry = [
                'itemtype' => self::class,
                'id' => $data['id'],
            ];
            if ($canedit) {
                $entry['row_class'] = 'cursor-pointer';
            }
            $entry['language'] = Dropdown::getLanguageName($data['language']);

            if ($canedit) {
                $entry['subject'] = sprintf(
                    '<a href="%s">%s</a>',
                    htmlescape(self::getFormURLWithID($data['id'])),
                    htmlescape($data['field'])
                );
            } else {
                $entry['subject'] = htmlescape($data['field']);
            }
            if (!empty($data['value'])) {
                $entry['subject'] .= Html::showToolTip(RichText::getEnhancedHtml($data['value']), ['display' => false]);
            }
            $entries[] = $entry;
        }

        TemplateRenderer::getInstance()->display('components/datatable.html.twig', [
            'datatable_id' => 'translationlist' . $rand,
            'is_tab' => true,
            'nofilter' => true,
            'columns' => [
                'language' => __('Language'),
                'subject' => __('Subject'),
            ],
            'formatters' => [
                'subject' => 'raw_html',
            ],
            'entries' => $entries,
            'total_number' => count($entries),
            'filtered_number' => count($entries),
            'showmassiveactions' => $canedit,
            'massiveactionparams' => [
                'num_displayed' => min($_SESSION['glpilist_limit'], count($entries)),
                'container'     => 'mass' . static::class . $rand,
                'specific_actions' => ['purge' => _x('button', 'Delete permanently')],
            ],
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
            ["items_id" => $item->getID()]
        );
    }
}
