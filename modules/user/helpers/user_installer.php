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
  public static function install() {
    Kohana::log("debug", "user_installer::install");
    $db = Database::instance();
    try {
      $base_version = ORM::factory("module")->where("name", "user")->find()->version;
    } catch (Exception $e) {
      if ($e->getMessage() == "Table modules does not exist in your database.") {
        $base_version = 0;
      } else {
        Kohana::log("error", $e);
        throw $e;
      }
    }
    Kohana::log("debug", "base_version: $base_version");

    if ($base_version == 0) {
      $db->query("CREATE TABLE IF NOT EXISTS `users` (
          `id` int(9) NOT NULL auto_increment,
          `name` varchar(255) NOT NULL,
          `display_name` char(255) NOT NULL,
          `email` int(9) default NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY(`display_name`))
        ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE IF NOT EXISTS`groups` (
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

      $user_module = ORM::factory("module")->where("name", "user")->find();
      $user_module->name = "user";
      $user_module->version = 1;
      $user_module->save();

      $user = ORM::factory("user")->where("display_name", "admin")->find();
      $user->name = "admin";
      $user->display_name = "Gallery Administrator";
      $user->save();

      foreach (array("administrator", "registered") as $group_name) {
        $group = ORM::factory("group")->where("name", $group_name)->find();
        $group->name = $group_name;
        $group->save();
        if (!$group->add($user)) {
          /** @todo replace this by throwing an exception once exceptions are implemented */
          Kohana::Log("debug", "{$user->name} was not added to {$group_name}");
        }
      }
    }
  }

  public static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS `users`;");
    $db->query("DROP TABLE IF EXISTS `groups`;");
    $db->query("DROP TABLE IF EXISTS `groups_users`;");
    $user_module = ORM::factory("module")->where("name", "user")->find();
    $user_module->delete();
  }
}