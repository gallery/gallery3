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
class User_Gallery_Driver implements User_Driver {
  /**
   * @see User_Driver::install
   */
  public function install() {
    Kohana::log("debug", "user_gallery_driver::install");
    $db = Database::instance();
    try {
      $base_version = ORM::factory("module")->where("name", "user")->find()->version;
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
      $db->query("CREATE TABLE IF NOT EXISTS `users` (
          `id` int(9) NOT NULL auto_increment,
          `name` varchar(255) NOT NULL,
          `display_name` char(255) NOT NULL,
          `password` varchar(128) NOT NULL,
          `logins` int(10) unsigned NOT NULL default '0',
          `last_login` int(10) unsigned NOT NULL default '0',
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

    }
  }

  /**
   * @see User_Driver::install
   *
   */
  public function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS `users`;");
    $db->query("DROP TABLE IF EXISTS `groups`;");
    $db->query("DROP TABLE IF EXISTS `groups_users`;");
  }

  /**
   * @see User_Driver::create_user
   */
  public function create_user($name, $display_name, $password, $email=null) {
    throw new Exception("@todo NOT_IMPLMENTED: create_user");
  }

  /**
   * @see User_Driver::update_user
   */
  public function update_user($id, $name, $display_name, $password, $email=null) {
    throw new Exception("@todo NOT_IMPLMENTED: update_user");
  }
  
  /**
   * @see User_Driver::get_user
   */
  public function get_user($id) {
    throw new Exception("@todo NOT_IMPLMENTED: get_user");
  }

  /**
   * @see User_Driver::get_user_by_name
   */
  public function get_user_by_name($name) {
    throw new Exception("@todo NOT_IMPLMENTED: get_user_by_name");
  }

  /**
   * @see User_Driver::delete_user
   */
  public function delete_user($id) {
    throw new Exception("@todo NOT_IMPLMENTED: delete_user");
  }

  /**
   * @see User_Driver::create_group
   */
  public function create_group($group_name) {
    throw new Exception("@todo NOT_IMPLMENTED: create_group");
  }

  /**
   * @see User_Driver::rename_group
   */
  public function rename_group($id, $new_name) {
    throw new Exception("@todo NOT_IMPLMENTED: rename_group");
  }

  /**
   * @see User_Driver::get_group
   */
  public function get_group($id) {
    throw new Exception("@todo NOT_IMPLMENTED: get_group");
  }

  /**
   * @see User_Driver::get_group_by_name
   */
  public function get_group_by_name($group_name) {
    throw new Exception("@todo NOT_IMPLMENTED: get_group_by_name");
  }

  /**
   * @see User_Driver::delete_group
   */
  public function delete_group($id) {
    throw new Exception("@todo NOT_IMPLMENTED: delete_group");
  }

  /**
   * @see User_Driver::add_user_to_group
   */
  public function add_user_to_group($group_id, $user_id) {
    throw new Exception("@todo NOT_IMPLMENTED: add_user_to_group");
  }

  /**
   * @see User_Driver::remove_user_from_group
   */
  public function remove_user_from_group($group_id, $user_id) {
    throw new Exception("@todo NOT_IMPLMENTED: remove_user_from_group");
  }
}