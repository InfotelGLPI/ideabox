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

use PluginDatainjectionCommonInjectionLib;
use PluginDatainjectionInjectionInterface;
use Search;

#[AllowDynamicProperties]
class IdeaboxInjection extends Ideabox
   implements PluginDatainjectionInjectionInterface {

    static function getTable($classname = null)
    {
        return Ideabox::getTable();
    }

   function isPrimaryType() {
      return true;
   }

   function connectedTo() {
      return [];
   }

   function getOptions($primary_type = '') {

      $tab = Search::getOptions(get_parent_class($this));

      //Specific to location
      $tab[4]['checktype'] = 'date';

      //$blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
      //Remove some options because some fields cannot be imported
      $notimportable            = [30, 80];
      $options['ignore_fields'] = $notimportable;
      $options['displaytype']   = ["user"           => [2],
                                        "multiline_text" => [3],
                                        "date"           => [4]];

      $tab = PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);

      return $tab;
   }

   /**
    * Standard method to delete an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param fields fields to add into glpi
    * @param options options used during creation
    */
   function deleteObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->deleteObject();
      return $lib->getInjectionResults();
   }

   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    *
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    */
   function addOrUpdateObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }

}
