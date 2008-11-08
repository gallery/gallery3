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
class auth_installer {
  public static function install() {
    Kohana::log("debug", "auth_installer::install");
    $db = Database::instance();
    try {
      $base_version = ORM::factory("module")->where("name", "auth")->find()->version;
    } catch (Exception $e) {
      if ($e->getCode() == E_DATABASE_ERROR) {
        $base_version = 0;
      } else {
        Kohana::log("error", $e);
        throw $e;
      }
    }
    Kohana::log("debug", "base_version: $base_version");

    if ($base_version == 0) {
      $db->query("CREATE TABLE IF NOT EXISTS `passwords` (
          `id` int(9) NOT NULL auto_increment,
          `user_id` int(9) NOT NULL,
          `password` varchar(1128) NOT NULL,
          `logins` int(10) unsigned NOT NULL default '0',
          `last_login` int(10) unsigned NOT NULL default '0',
          PRIMARY KEY (`id`),
          UNIQUE KEY (`user_id`))
          ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $user_module = ORM::factory("module")->where("name", "auth")->find();
      $user_module->name = "auth";
      $user_module->version = 1;
      $user_module->save();

      $user = ORM::factory("user")->where("name", "admin")->find();
      Auth::instance()->set_user_password($user->id, "admin");
    }
  }

  public static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS `passwords`;");
    $auth_module = ORM::factory("module")->where("name", "auth")->find();
    $auth_module->delete();
  }
}