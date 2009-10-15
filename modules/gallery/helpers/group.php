<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
   * @see Identity_Driver::create.
   */
  static function create($name) {
    return Identity::instance()->create_group($name);
  }

  /**
   * @see Identity_Driver::everbody.
   */
  static function everybody() {
    return Identity::instance()->everybody();
  }

  /**
   * @see Identity_Driver::registered_users.
   */
  static function registered_users() {
    return Identity::instance()->everybody();
  }

  /**
   * Look up a group by id.
   * @param integer      $id the user id
   * @return Group_Definition  the group object, or null if the id was invalid.
   */
  static function lookup($id) {
    return Identity::instance()->lookup_group_by_field("id", $id);
  }

  /**
   * Look up a group by name.
   * @param integer      $id the group name
   * @return Group_Definition  the group object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return Identity::instance()->lookup_group_by_field("name", $name);
  }

  /**
   * @see Identity_Driver::get_group_list.
   */
  static function get_group_list($filter=array()) {
    return Identity::instance()->get_group_list($filter);
  }

  /**
   * @see Identity_Driver::get_edit_rules.
   */
  static function get_edit_rules() {
    return Identity::instance()->get_edit_rules("group");
  }
}
