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
interface IdentityProvider_Driver {
  /**
   * Return the guest user.
   *
   * @return User_Definition the user object
   */
  public function guest();

  /**
   * Return the primary admin user.
   *
   * @return User_Definition the user object
   */
  public function admin_user();

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @param string  $email
   * @return User_Definition the user object
   */
  public function create_user($name, $full_name, $password, $email);

  /**
   * Is the password provided correct?
   *
   * @param user User_Definition the user object
   * @param string $password a plaintext password
   * @return boolean true if the password is correct
   */
  public function is_correct_password($user, $password);

  /**
   * Look up a user by id.
   * @param integer $id
   * @return User_Definition the user object, or null if the name was invalid.
   */
  public function lookup_user($id);

  /**
   * Look up a user by name.
   * @param string $name
   * @return User_Definition the user object, or null if the name was invalid.
   */
  public function lookup_user_by_name($name);

  /**
   * Create a new group.
   *
   * @param string $name
   * @return Group_Definition the group object
   */
  public function create_group($name);

  /**
   * The group of all possible visitors.  This includes the guest user.
   *
   * @return Group_Definition the group object
   */
  public function everybody();

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @return Group_Definition the group object
   */
  public function registered_users();

  /**
   * List the users
   * @param array $ids array of ids to return the user objects for
   * @return array the user list.
   */
  public function get_user_list($ids);

  /**
   * Look up a group by id.
   * @param integer $id id
   * @return Group_Definition the user object, or null if the name was invalid.
   */
  public function lookup_group($id);

  /**
   * Look up the group by name.
   * @param string $name the name of the group to locate
   * @return Group_Definition
   */
  public function lookup_group_by_name($name);

  /**
   * List the groups defined in the Identity Provider
   */
  public function groups();

  /**
   * Add the user to the specified group
   * @param User_Definition  the user to add
   * @param Group_Definition the target group
   */
  public function add_user_to_group($user, $group);

  /**
   * Remove the user to the specified group
   * @param User_Definition  the user to remove
   * @param Group_Definition the owning group
   */
  public function remove_user_from_group($user, $group);
} // End Identity Driver Definition

interface Group_Definition {}

interface User_Definition {}
