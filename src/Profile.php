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

use CommonGLPI;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Html;
use ProfileRight;
use Session;

class Profile extends \Profile
{
    public static $rightname = "profile";

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            return self::createTabEntry(Ideabox::getTypeName(2));
        }
        return '';
    }

    public static function getIcon()
    {
        return "ti ti-bulb";
    }



    public static function createFirstAccess($ID)
    {

        self::addDefaultProfileInfos(
            $ID,
            ['plugin_ideabox'             => 127,
                'plugin_ideabox_open_ticket' => 1],
            true
        );
    }


    /**
     * @param      $profiles_id
     * @param      $rights
     * @param bool $drop_existing
     *
     * @internal param $profile
     */
    public static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false)
    {

        $profileRight = new ProfileRight();
        $dbu          = new DbUtils();
        foreach ($rights as $right => $value) {
            if ($dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
            ) && $drop_existing) {
                $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
            }
            if (!$dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
            )) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name']        = $right;
                $myright['rights']      = $value;
                $profileRight->add($myright);

                //Add right to the current session
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        if (!$item instanceof \Profile || !self::canView()) {
            return false;
        }

        $profile = new \Profile();
        $profile->getFromDB($item->getID());

        $twig = TemplateRenderer::getInstance();
        $twig->display('@ideabox/profile.html.twig', [
            'id'      => $item->getID(),
            'profile' => $profile,
            'title'   => self::getTypeName(Session::getPluralNumber()),
            'rights'  => [
                [
                    'itemtype' => Ideabox::getType(),
                    'label'    => Ideabox::getTypeName(Session::getPluralNumber()),
                    'field'    => Ideabox::$rightname,
                ],
                [
                    'itemtype' => Ideabox::getType(),
                    'label'    => __s('Associable items to a ticket'),
                    'field'    => 'plugin_ideabox_open_ticket',
                    [
                        READ  => __('Read'),
                        ],
                ],
            ],
        ]);

        return true;
    }

    /**
     * @param bool $all
     *
     * @return array
     */
    public static function getAllRights($all = false)
    {
        $rights = [
            ['itemtype' => Ideabox::class,
                'label'    => _n('Idea box', 'Ideas box', 2, 'ideabox'),
                'field'    => 'plugin_ideabox',
            ],
        ];

        if ($all) {
            $rights[] = ['itemtype' => Ideabox::class,
                'label'    => __s('Associable items to a ticket'),
                'field'    => 'plugin_ideabox_open_ticket'];
        }

        return $rights;
    }

    /**
     * Init profiles
     *
     * @param $old_right
     *
     * @return int
     */

    public static function translateARight($old_right)
    {
        switch ($old_right) {
            case '':
                return 0;
            case 'r':
                return READ;
            case 'w':
                return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
            case '0':
            case '1':
                return $old_right;

            default:
                return 0;
        }
    }

    /**
     * @param $profiles_id the profile ID
     *
     * @return bool
     * @since 0.85
     * Migration rights from old system to the new one for one profile
     *
     */
    public static function migrateOneProfile($profiles_id)
    {
        global $DB;
        //Cannot launch migration if there's nothing to migrate...
        if (!$DB->tableExists('glpi_plugin_ideabox_profiles')) {
            return true;
        }

        $it = $DB->request([
            'FROM' => 'glpi_plugin_ideabox_profiles',
            'WHERE' => ['profiles_id' => $profiles_id],
        ]);
        foreach ($it as $profile_data) {
            $matching       = ['ideabox'     => 'plugin_ideabox',
                'open_ticket' => 'plugin_ideabox_open_ticket'];
            $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
            foreach ($matching as $old => $new) {
                if (!isset($current_rights[$old])) {
                    $DB->update('glpi_profilerights', ['rights' => self::translateARight($profile_data[$old])], [
                        'name'        => $new,
                        'profiles_id' => $profiles_id,
                    ]);
                }
            }
        }
    }

    /**
     * Initialize profiles, and migrate it necessary
     */
    public static function initProfile()
    {
        global $DB;
        $profile = new self();
        $dbu     = new DbUtils();
        //Add new rights in glpi_profilerights table
        foreach ($profile->getAllRights(true) as $data) {
            if ($dbu->countElementsInTable(
                "glpi_profilerights",
                ["name" => $data['field']]
            ) == 0) {
                ProfileRight::addProfileRights([$data['field']]);
            }
        }

        //Migration old rights in new ones
        $it = $DB->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_profiles',
        ]);
        foreach ($it as $prof) {
            self::migrateOneProfile($prof['id']);
        }
        $it = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $_SESSION['glpiactiveprofile']['id'],
                'name' => ['LIKE', '%plugin_ideabox%'],
            ],
        ]);
        foreach ($it as $prof) {
            if (isset($_SESSION['glpiactiveprofile'])) {
                $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
            }
        }
    }
}
