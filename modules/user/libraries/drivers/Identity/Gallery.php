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
   * @see Identity_Driver::guest.
   */
  public function guest() {
    return new Gallery_User(model_cache::get("user", 1));
  }

  /**
   * @see Identity_Driver::create_user.
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
    $user->add($this->everybody()->_uncloaked());
    $user->add($this->registered_users()->_uncloaked());

    $user->save();
    return new Gallery_User($user);
  }

  /**
   * @see Identity_Driver::is_correct_password.
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
   * @see Identity_Driver::hash_password.
   */
  public function hash_password($password) {
    require_once(MODPATH . "user/lib/PasswordHash.php");
    $hashGenerator = new PasswordHash(10, true);
    return $hashGenerator->HashPassword($password);
  }

  /**
   * @see Identity_Driver::lookup_user_by_field.
   */
  public function lookup_user_by_field($field_name, $value) {
    try {
      $user = model_cache::get("user", $value, $field_name);
      if ($user->loaded) {
        return new Gallery_User($user);
      }
    } catch (Exception $e) {
      if (strpos($e->getMessage(), "MISSING_MODEL") === false) {
       throw $e;
      }
    }
    return null;
  }

  /**
   * @see Identity_Driver::create_group.
   */
  public function create_group($name) {
    $group = ORM::factory("group")->where("name", $name)->find();
    if ($group->loaded) {
      throw new Exception("@todo GROUP_ALREADY_EXISTS $name");
    }

    $group->name = $name;
    $group->save();

    return new Gallery_Group($group);
  }

  /**
   * @see Identity_Driver::everybody.
   */
  public function everybody() {
    return new Gallery_Group(model_cache::get("group", 1));
  }

  /**
   * @see Identity_Driver::registered_users.
   */
  public function registered_users() {
    return new Gallery_Group(model_cache::get("group", 2));
  }

  /**
   * @see Identity_Driver::lookup_group_by_field.
   */
  public function lookup_group_by_field($field_name, $value) {
    try {
      $group = model_cache::get("group", $value, $field_name);
      if ($group->loaded) {
        return new Gallery_Group($group);
      }
    } catch (Exception $e) {
      if (strpos($e->getMessage(), "MISSING_MODEL") === false) {
       throw $e;
      }
    }
    return null;
  }

  /**
   * @see Identity_Driver::get_user_list.
   */
  public function get_user_list($filter=array()) {
    $results = $this->_do_search("user", $filter);
    $users = array();
    foreach ($results->as_array() as $user) {
      $users[] = new Gallery_User($user);
    }
    return $users;
  }

  /**
   * @see Identity_Driver::get_group_list.
   */
  public function get_group_list($filter=array()) {
    $results = $this->_do_search("group", $filter);
    $groups = array();
    foreach ($results->as_array() as $group) {
      $groups[] = new Gallery_Group($group);
    }
    return $groups;
  }

  /**
   * @see Identity_Driver::get_edit_rules.
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

/**
 * User Data wrapper
 */
class Gallery_User extends User_Definition {
  /*
   *  Not for general user, allows the back-end to easily create the interface object
   */
  function __construct($user) {
    $this->user = $user;
  }

  public function save() {
    $this->user->save();
  }

  public function delete() {
    $this->user->delete();
  }

}

/**
 * Group Data wrapper
 */
class Gallery_Group extends Group_Definition {
  /*
   *  Not for general user, allows the back-end to easily create the interface object
   */
  function __construct($group) {
    $this->group = $group;
  }

  public function save() {
    $this->group->save();
  }

  public function delete() {
    $this->group->delete();
  }

  public function add($user) {
    $this->group->add($user->_uncloaked());
  }

  public function remove($user) {
    $this->group->remove($user->_uncloaked());
  }
}
