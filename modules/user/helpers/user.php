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
 * This is the API for handling users.
 *
 * Note: by design, this class does not do any permission checking.
 */
class user_Core {
  /**
   * Initialize the provider so it is ready to use
   */
  static function activate() {
    $db = Database::instance();
    $db->query("CREATE TABLE IF NOT EXISTS {users} (
                 `id` int(9) NOT NULL auto_increment,
                 `name` varchar(32) NOT NULL,
                 `full_name` varchar(255) NOT NULL,
                 `password` varchar(64) NOT NULL,
                 `login_count` int(10) unsigned NOT NULL DEFAULT 0,
                 `last_login` int(10) unsigned NOT NULL DEFAULT 0,
                 `email` varchar(64) default NULL,
                 `admin` BOOLEAN default 0,
                 `guest` BOOLEAN default 0,
                 `hash` char(32) default NULL,
                 `url` varchar(255) default NULL,
                 `locale` char(10) default NULL,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`hash`),
                 UNIQUE KEY(`name`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE IF NOT EXISTS {groups} (
                 `id` int(9) NOT NULL auto_increment,
                 `name` char(64) default NULL,
                 `special` BOOLEAN default 0,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`name`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE IF NOT EXISTS {groups_users} (
                 `group_id` int(9) NOT NULL,
                 `user_id` int(9) NOT NULL,
                 PRIMARY KEY (`group_id`, `user_id`),
                 UNIQUE KEY(`user_id`, `group_id`))
               DEFAULT CHARSET=utf8;");

    $everybody = group::create("Everybody");
    $everybody->special = true;
    $everybody->save();

    $registered = group::create("Registered Users");
    $registered->special = true;
    $registered->save();

    $guest = user::create("guest", "Guest User", "");
    $guest->guest = true;
    $guest->remove($registered);
    $guest->save();

    $admin = user::create("admin", "Gallery Administrator", "admin");
    $admin->admin = true;
    $admin->save();

    // Let the admin own everything
    $db->query("update {items} set owner_id = {$admin->id}");

    $root = ORM::factory("item", 1);
    access::allow($everybody, "view", $root);
    access::allow($everybody, "view_full", $root);

    access::allow($registered, "view", $root);
    access::allow($registered, "view_full", $root);
  }

  /**
   * Cleanup up this provider so it is unavailable for use and won't conflict with the current driver
   */
  static function deactivate() {
    // Delete all users and groups so that we give other modules an opportunity to clean up
    foreach (ORM::factory("user")->find_all() as $user) {
      $user->delete();
    }

    foreach (ORM::factory("group")->find_all() as $group) {
      $group->delete();
    }

    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS {users};");
    $db->query("DROP TABLE IF EXISTS {groups};");
    $db->query("DROP TABLE IF EXISTS {groups_users};");
  }

  /**
   * Return the guest user.
   *
   * @return User_Model the user object
   */
  static function guest() {
    return model_cache::get("user", 1);
  }

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @return User_Definition the user object
   */
  static function create($name, $full_name, $password) {
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
   * Hash the password to the internal value
   * @param string   $password the user password
   * @param string   The hashed equivalent
   */
  static function hash_password($password) {
    require_once(MODPATH . "user/lib/PasswordHash.php");
    $hashGenerator = new PasswordHash(10, true);
    return $hashGenerator->HashPassword($password);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Definition  the user object, or null if the id was invalid.
   */
  static function lookup($id) {
    return self::lookup_by_field("id", $id);
  }

  /**
   * Look up a user by name.
   * @param integer      $name the user name
   * @return User_Definition  the user object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return self::lookup_by_field("name", $name);
  }

  static function lookup_by_field($field_name, $value) {
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
}