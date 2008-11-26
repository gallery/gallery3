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
class user_installer {
  protected $has_many = array('items');

  public static function install() {
    Kohana::log("debug", "user_installer::install");
    $db = Database::instance();
    $version = module::get_version("user");
    Kohana::log("debug", "version: $version");

    if ($version == 0) {
      $db->query("CREATE TABLE IF NOT EXISTS `users` (
          `id` int(9) NOT NULL auto_increment,
          `name` varchar(32) NOT NULL,
          `display_name` varchar(255) NOT NULL,
          `password` varchar(128) NOT NULL,
          `login_count` int(10) unsigned NOT NULL DEFAULT 0,
          `last_login` int(10) unsigned NOT NULL DEFAULT 0,
          `email` varchar(255) default NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY(`display_name`))
        ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE IF NOT EXISTS `groups` (
          `id` int(9) NOT NULL auto_increment,
          `name` char(255) default NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY(`name`))
        ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE IF NOT EXISTS `groups_users` (
          `group_id` int(9) NOT NULL,
          `user_id` int(9) NOT NULL,
          PRIMARY KEY (`group_id`, `user_id`),
          UNIQUE KEY(`user_id`, `group_id`))
        ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      module::set_version("user", 1);

      // @todo: get this info from the installer
      $admin = user::create("admin", "Gallery Administrator", "admin");
      $user = user::create("joe", "Joe User", "joe");

      $registered = group::create("Registered Users");
      $registered->add($admin);
      $registered->add($user);

      // Let the admin own everything
      $db->query("UPDATE `items` SET `owner_id` = {$admin->id} WHERE `owner_id` IS NULL");
    }
  }

  public static function uninstall() {
    try {
      Session::instance()->destroy();
    } catch (Exception $e) {
    }
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS `users`;");
    $db->query("DROP TABLE IF EXISTS `groups`;");
    $db->query("DROP TABLE IF EXISTS `groups_users`;");
    module::delete("user");
  }
}