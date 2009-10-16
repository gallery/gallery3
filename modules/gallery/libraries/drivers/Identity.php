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
   * @return User_Definition the user object
   */
  public function guest();

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @return User_Definition the user object
   */
  public function create_user($name, $full_name, $password);

  /**
   * Is the password provided correct?
   *
   * @param user User_Definition the user object
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
   * Look up a user by by search the specified field.
   * @param string      search field
   * @param string      search value
   * @return User_Definition the user object, or null if the name was invalid.
   */
  public function lookup_user_by_field($field, $value);

  /**
   * Create a new group.
   *
   * @param string  $name
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
   * @param array      array of ids to return the user objects for
   * @return array     the user list.
   */
  public function get_user_list($ids);

} // End Identity Driver Definition

/**
 * User Data wrapper
 */
abstract class User_Definition {
  protected $user;
  public function __get($column) {
    switch ($column) {
    case "id":
    case "name":
    case "full_name":
    case "password":
    case "login_count":
    case "last_login":
    case "email":
    case "admin":
    case "guest":
    case "hash":
    case "url":
    case "locale":
    case "groups":
    case "hashed_password":
      return $this->user->$column;
    default:
      throw new Exception("@todo UNSUPPORTED FIELD: $column");
      break;
    }
  }

  public function __set($column, $value) {
    switch ($column) {
    case "id":
    case "groups":
      throw new Exception("@todo READ ONLY FIELD: $column");
      break;
    case "name":
    case "full_name":
    case "hashed_password":
    case "password":
    case "login_count":
    case "last_login":
    case "email":
    case "admin":
    case "guest":
    case "hash":
    case "url":
    case "locale":
      $this->user->$column = $value;
      break;
    default:
      throw new Exception("@todo UNSUPPORTED FIELD: $column");
      break;
    }
  }

  public function __isset($column) {
    return isset($this->user->$column);
  }

  public function __unset($column) {
    switch ($column) {
    case "id":
    case "groups":
      throw new Exception("@todo READ ONLY FIELD: $column");
      break;
    case "name":
    case "full_name":
    case "password":
    case "login_count":
    case "last_login":
    case "email":
    case "admin":
    case "guest":
    case "hash":
    case "url":
    case "locale":
    case "hashed_password":
      unset($this->user->$column);
      break;
    default:
      throw new Exception("@todo UNSUPPORTED FIELD: $column");
      break;
    }
  }

  /**
   * Return a url to the user's avatar image.
   * @param integer $size the target size of the image (default 80px)
   * @return string a url
   */
  abstract public function avatar_url($size=80, $default=null);

  /**
   * Return the best version of the user's name.  Either their specified full name, or fall back
   * to the user name.
   * @return string
   */
  abstract public function display_name();

  /**
   * Return the internal user object without the wrapper.
   * This method is used by implementing classes to access the internal user object.
   * Consider it pseudo private and only declared public as PHP as not internal or friend modifier
   */
  public function _uncloaked() {
    return $this->user;
  }

  abstract public function save();
  abstract public function delete();
}

/**
 * Group Data wrapper
 */
abstract class Group_Definition {
  protected $group;

  public function __get($column) {
    switch ($column) {
    case "id":
    case "name":
    case "special":
    case "users":
      return $this->group->$column;
    default:
      throw new Exception("@todo UNSUPPORTED FIELD: $column");
      break;
    }
  }

  public function __set($column, $value) {
    switch ($column) {
    case "id":
    case "users":
      throw new Exception("@todo READ ONLY FIELD: $column");
      break;
    case "name":
    case "special":
      $this->group->$column = $value;
    default:
      throw new Exception("@todo UNSUPPORTED FIELD: $column");
      break;
    }
  }

  public function __isset($column) {
    return isset($this->group->$column);
  }

  public function __unset($column) {
    switch ($column) {
    case "id":
    case "users":
      throw new Exception("@todo READ ONLY FIELD: $column");
      break;
    case "name":
    case "special":
      unset($this->group->$column);
    default:
      throw new Exception("@todo UNSUPPORTED FIELD: $column");
      break;
    }
  }

  /**
   * Return the internal group object without the wrapper.
   * This method is used by implementing classes to access the internal group object.
   * Consider it pseudo private and only declared public as PHP as not internal or friend modifier
   */
  public function _uncloaked() {
    return $this->group;
  }

  abstract public function save();
  abstract public function delete();
  abstract public function add($user);
  abstract public function remove($user);
}
