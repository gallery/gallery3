<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * This is the API for handling groups.
 *
 * Note: by design, this class does not do any permission checking.
 */
class group_Core {
  /**
   * The group of all possible visitors.  This includes the guest user.
   *
   * @return Group_Definition the group object
   */
  static function everybody() {
    return model_cache::get("group", 1);
  }

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @return Group_Definition the group object
   */
  static function registered_users() {
    return model_cache::get("group", 2);
  }

  /**
   * Look up a group by id.
   * @param integer      $id the user id
   * @return Group_Definition  the group object, or null if the id was invalid.
   */
  static function lookup($id) {
    return self::_lookup_by_field("id", $id);
  }

  /**
   * Look up a group by name.
   * @param integer      $id the group name
   * @return Group_Definition  the group object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return self::_lookup_by_field("name", $name);
  }

  /**
   * Search the groups by the field and value.
   * @param string      $field_name column to look up the user by
   * @param string      $value value to match
   * @return Group_Definition  the group object, or null if the name was invalid.
   */
  private static function _lookup_by_field($field_name, $value) {
    try {
      $group = model_cache::get("group", $value, $field_name);
      if ($group->loaded()) {
        return $group;
      }
    } catch (Exception $e) {
      if (strpos($e->getMessage(), "MISSING_MODEL") === false) {
        throw $e;
      }
    }
    return null;
  }
}
