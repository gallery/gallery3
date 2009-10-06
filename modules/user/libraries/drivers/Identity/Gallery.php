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
/*
 * Based on the Cache_Sqlite_Driver developed by the Kohana Team
 */
class Identity_Gallery_Driver implements Identity_Driver {
  /**
   * Return the guest user.
   *
   * @todo consider caching
   *
   * @return User_Model
   */
  public function guest() {
    return model_cache::get("user", 1);
  }

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @return User_Model
   */
  public function create_user($name, $full_name, $password) {
    $user = ORM::factory("user")->where("name", $name)->find();
    if ($user->loaded) {
      throw new Exception("@todo USER_ALREADY_EXISTS $name");
    }

    $user->name = $name;
    $user->full_name = $full_name;
    $user->password = $password;

    // Required groups
    $user->add(group::everybody());
    $user->add(group::registered_users());

    $user->save();
    return $user;
  }

  /**
   * Is the password provided correct?
   *
   * @param user User Model
   * @param string $password a plaintext password
   * @return boolean true if the password is correct
   */
  public function is_correct_password($user, $password) {
    $valid = $user->password;

    // Try phpass first, since that's what we generate.
    if (strlen($valid) == 34) {
      require_once(MODPATH . "user/lib/PasswordHash.php");
      $hashGenerator = new PasswordHash(10, true);
      return $hashGenerator->CheckPassword($password, $valid);
    }

    $salt = substr($valid, 0, 4);
    // Support both old (G1 thru 1.4.0; G2 thru alpha-4) and new password schemes:
    $guess = (strlen($valid) == 32) ? md5($password) : ($salt . md5($salt . $password));
    if (!strcmp($guess, $valid)) {
      return true;
    }

    // Passwords with <&"> created by G2 prior to 2.1 were hashed with entities
    $sanitizedPassword = html::specialchars($password, false);
    $guess = (strlen($valid) == 32) ? md5($sanitizedPassword)
          : ($salt . md5($salt . $sanitizedPassword));
    if (!strcmp($guess, $valid)) {
      return true;
    }

    return false;
  }

  /**
   * Create the hashed passwords.
   * @param string $password a plaintext password
   * @return string hashed password
   */
  public function hash_password($password) {
    require_once(MODPATH . "user/lib/PasswordHash.php");
    $hashGenerator = new PasswordHash(10, true);
    return $hashGenerator->HashPassword($password);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Model  the user object, or null if the id was invalid.
   */
  public function lookup_user($id) {
    $user = model_cache::get("user", $id);
    if ($user->loaded) {
      return $user;
    }
    return null;
  }

  /**
   * Look up a user by field value.
   * @param string      search field
   * @param string      search value
   * @return User_Core  the user object, or null if the name was invalid.
   */
  public function lookup_user_by_field($field_name, $value) {
    try {
      $user = model_cache::get("user", $value, $field_name);
      if ($user->loaded) {
        return $user;
      }
    } catch (Exception $e) {
      if (strpos($e->getMessage(), "MISSING_MODEL") === false) {
       throw $e;
      }
    }
    return null;
  }

  /**
   * Create a new group.
   *
   * @param string  $name
   * @return Group_Model
   */
  public function create_group($name) {
    $group = ORM::factory("group")->where("name", $name)->find();
    if ($group->loaded) {
      throw new Exception("@todo GROUP_ALREADY_EXISTS $name");
    }

    $group->name = $name;
    $group->save();

    return $group;
  }

  /**
   * The group of all possible visitors.  This includes the guest user.
   *
   * @return Group_Model
   */
  public function everybody() {
    return model_cache::get("group", 1);
  }

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @return Group_Model
   */
  public function registered_users() {
    return model_cache::get("group", 2);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Model  the user object, or null if the id was invalid.
   */
  public function lookup_group($id) {
    $group = model_cache::get("group", $id);
    if ($group->loaded) {
      return $group;
    }
    return null;
  }

  /**
   * Look up a group by name.
   * @param integer      $id the group name
   * @return Group_Model  the group object, or null if the name was invalid.
   */
  public function lookup_group_by_name($name) {
    try {
      $group = model_cache::get("group", $name, "name");
      if ($group->loaded) {
        return $group;
      }
    } catch (Exception $e) {
      if (strpos($e->getMessage(), "MISSING_MODEL") === false) {
       throw $e;
      }
    }
    return null;
  }

  /**
   * List the users
   * @param mixed      options to apply to the selection of the user
   * @return array     the group list.
   */
  public function list_users($filter=array()) {
    return $this->_do_search("user", $filter);
  }


  /**
   * List the groups
   * @param mixed      options to apply to the selection of the user
   * @return array     the group list.
   */
  public function list_groups($filter=array()) {
    return $this->_do_search("group", $filter);
  }

  /**
   * Return the edit rules associated with an group.
   *
   * @param  string   $object_type to return rules for ("user"|"group")
   * @return stdClass containing the rules
   */
  public function get_edit_rules($object_type) {
    return (object)ORM::factory($object_type)->rules;
  }

  /**
   * Build the query based on the supplied filters for the specified model.
   * @param  string   $object_type to return rules for ("user"|"group")
   * @param  mixed    $filters options to apply to the selection.
   */
  private function _do_search($object_type, $filter) {
    $object = ORM::factory($object_type);

    foreach ($filter as $method => $args) {
      switch ($method) {
      case "in":
        $object->in($args[0], $args[1]);
        break;
      default:
        $object->$method($args);
      }
    }

    return $object->find_all();
  }

} // End Identity Gallery Driver