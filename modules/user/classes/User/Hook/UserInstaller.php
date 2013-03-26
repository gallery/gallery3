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
class user_installer {
  static function can_activate() {
    return array("warn" => array(IdentityProvider::confirmation_message()));
  }

  static function activate() {
    IdentityProvider::change_provider("user");
    // Set the latest version in initialize() below
  }

  static function upgrade($version) {
    if ($version == 1) {
      module::set_var("user", "mininum_password_length", 5);
      module::set_version("user", $version = 2);
    }

    if ($version == 2) {
      db::build()
        ->update("users")
        ->set("email", "unknown@unknown.com")
        ->where("guest", "=", 0)
        ->and_open()
        ->where("email", "IS", null)
        ->or_where("email", "=", "")
        ->close()
        ->execute();
      module::set_version("user", $version = 3);
    }

    if ($version == 3) {
      $password_length = module::get_var("user", "mininum_password_length", 5);
      module::set_var("user", "minimum_password_length", $password_length);
      module::clear_var("user", "mininum_password_length");
      module::set_version("user", $version = 4);
    }
  }

  static function uninstall() {
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

  static function initialize() {
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

    $everybody = ORM::factory("group");
    $everybody->name = "Everybody";
    $everybody->special = true;
    $everybody->save();

    $registered = ORM::factory("group");
    $registered->name = "Registered Users";
    $registered->special = true;
    $registered->save();

    $guest = ORM::factory("user");
    $guest->name = "guest";
    $guest->full_name = "Guest User";
    $guest->password = "";
    $guest->guest = true;
    $guest->save();

    $admin = ORM::factory("user");
    $admin->name = "admin";
    $admin->full_name = "Gallery Administrator";
    $admin->password = "admin";
    $admin->email = "unknown@unknown.com";
    $admin->admin = true;
    $admin->save();

    $root = ORM::factory("item", 1);
    access::allow($everybody, "view", $root);
    access::allow($everybody, "view_full", $root);

    access::allow($registered, "view", $root);
    access::allow($registered, "view_full", $root);

    module::set_var("user", "minimum_password_length", 5);
  }
}