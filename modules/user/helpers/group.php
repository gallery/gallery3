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
  const REGISTERED_USERS = 1;

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

    // Create the view column for this group in the items table.
    Database::instance()->query("ALTER TABLE `items` ADD `view_{$group->id}` BOOLEAN DEFAULT 0");

    return $group;
  }

  /**
   * Delete a group
   *
   * @param string $name the group name
   */
  static function delete($id) {
    $group = ORM::factory("group", $id);

    if ($group->loaded) {
      // Drop the view column for this group in the items table.
      Database::instance()->query("ALTER TABLE `items` DROP `view_{$group->id}`");
      $group->delete();
    }
  }

  /**
   * Remove a user from a group
   *
   * @param integer $group_id the id of the group
   * @param integer $user_id the id of the user
   * @return Group_Model
   */
  static function remove_user($group_id, $user_id) {
    $group = ORM::factory("group", $group_id);
    if (!$group->loaded) {
      throw new Exception("@todo MISSING_GROUP $group_id");
    }

    $user = ORM::factory("user", $user_id);
    if (!$user->loaded) {
      throw new Exception("@todo MISSING_USER $user_id");
    }

    $group->remove($user);
    return $group;
  }


  /**
   * Add a user to a group
   *
   * @param integer $group_id the id of the group
   * @param integer $user_id the id of the user
   * @return Group_Model
   */
  static function add_user($group_id, $user_id) {
    $group = ORM::factory("group", $group_id);
    if (!$group->loaded) {
      throw new Exception("@todo MISSING_GROUP $group_id");
    }

    $user = ORM::factory("user", $user_id);
    if (!$user->loaded) {
      throw new Exception("@todo MISSING_USER $user_id");
    }

    $group->add($user);
    return $group;
  }
}