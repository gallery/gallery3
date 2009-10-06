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
interface Identity_Driver {
  /**
   * Return the guest user.
   *
   * @todo consider caching
   *
   * @return User_Model
   */
  public function guest();

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @return User_Core
   */
  public function create_user($name, $full_name, $password);

  /**
   * Is the password provided correct?
   *
   * @param user User Model
   * @param string $password a plaintext password
   * @return boolean true if the password is correct
   */
  public function is_correct_password($user, $password);

  /**
   * Create the hashed passwords.
   * @param string $password a plaintext password
   * @return string hashed password
   */
  public function hash_password($password);
  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Core  the user object, or null if the id was invalid.
   */
  public function lookup_user($id);

  /**
   * Look up a user by name.
   * @param string      search field
   * @param string      search value
   * @return User_Core  the user object, or null if the name was invalid.
   */
  public function lookup_user_by_field($field, $value);

  /**
   * Create a new group.
   *
   * @param string  $name
   * @return Group_Model
   */
  public function create_group($name);

  /**
   * The group of all possible visitors.  This includes the guest user.
   *
   * @return Group_Model
   */
  public function everybody();

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @return Group_Model
   */
  public function registered_users();

  /**
   * Look up a group by id.
   * @param integer      $id the user id
   * @return Group_Model  the group object, or null if the id was invalid.
   */
  public function lookup_group($id);

  /**
   * Look up a group by name.
   * @param integer      $id the group name
   * @return Group_Model  the group object, or null if the name was invalid.
   */
  public function lookup_group_by_name($name);

  /**
   * List the users
   * @param mixed      options to apply to the selection of the user
   * @return array     the group list.
   */
  public function list_users($filter=array());

  /**
   * List the groups
   * @param mixed      options to apply to the selection of the user
   * @return array     the group list.
   */
  public function list_groups($filter=array());

  /**
   * Return the edit rules associated with an group.
   *
   * @param  string   $object_type to return rules for ("user"|"group")
   * @return stdClass containing the rules
   */
  public function get_edit_rules($object_type);
} // End User Driver