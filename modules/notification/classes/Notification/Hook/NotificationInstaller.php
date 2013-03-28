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
class notification_installer {
  static function install() {
    $db = Database::instance();
    $db->query("CREATE TABLE IF NOT EXISTS {subscriptions} (
               `id` int(9) NOT NULL auto_increment,
               `item_id` int(9) NOT NULL,
               `user_id` int(9) NOT NULL,
               PRIMARY KEY (`id`),
               UNIQUE KEY (`item_id`, `user_id`),
               UNIQUE KEY (`user_id`, `item_id`))
               DEFAULT CHARSET=utf8;");
    $db->query("CREATE TABLE IF NOT EXISTS {pending_notifications} (
               `id` int(9) NOT NULL auto_increment,
               `locale` char(10) default NULL,
               `email` varchar(128) NOT NULL,
               `subject` varchar(255) NOT NULL,
               `text` text,
               PRIMARY KEY (`id`))
               DEFAULT CHARSET=utf8;");
  }

  static function upgrade($version) {
    $db = Database::instance();
    if ($version == 1) {
      $db->query("ALTER TABLE {pending_notifications} ADD COLUMN `locale` char(10) default NULL");
      module::set_version("notification", $version = 2);
    }
  }

  static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS {subscriptions};");
    $db->query("DROP TABLE IF EXISTS {pending_notifications};");
  }
}
