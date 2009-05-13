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
class user_installer {
  static function install() {
    $db = Database::instance();
    $version = module::get_version("user");

    if ($version == 0) {
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
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE IF NOT EXISTS {groups} (
                   `id` int(9) NOT NULL auto_increment,
                   `name` char(64) default NULL,
                   `special` BOOLEAN default 0,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE IF NOT EXISTS {groups_users} (
                   `group_id` int(9) NOT NULL,
                   `user_id` int(9) NOT NULL,
                   PRIMARY KEY (`group_id`, `user_id`),
                   UNIQUE KEY(`user_id`, `group_id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

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
      $db->update("items", array("owner_id" => $admin->id), array("owner_id" => "IS NULL"));
      module::set_version("user", 1);

      $root = ORM::factory("item", 1);
      access::allow($everybody, "view", $root);
      access::allow($everybody, "view_full", $root);

      access::allow($registered, "view", $root);
      access::allow($registered, "view_full", $root);
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

    try {
      Session::instance()->destroy();
    } catch (Exception $e) {
    }
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS {users};");
    $db->query("DROP TABLE IF EXISTS {groups};");
    $db->query("DROP TABLE IF EXISTS {groups_users};");
    module::delete("user");
  }
}