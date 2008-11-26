<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
   * Create a new group.
   *
   * @param string  $name
   * @return Group_Model
   */
  static function create($name) {
    $group = ORM::factory("group");
    if ($group->loaded) {
      throw new Exception("@todo GROUP_ALREADY_EXISTS $name");
    }

    $group->name = $name;
    $group->save();
    return $group;
  }

  /**
   * Delete a group
   *
   * @param string $name the group name
   */
  static function delete($name) {
    ORM::factory("group")->where("name", $name)->find()->delete();
  }
}