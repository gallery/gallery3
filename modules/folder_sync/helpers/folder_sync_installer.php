<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
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
class folder_sync_installer {
  static function install() {
    $db = Database::instance();
    $db->query("CREATE TABLE {folder_sync_entries} (
                  `id` int(9) NOT NULL auto_increment,
                  `checked` boolean default 0,
                  `is_directory` boolean default 0,
                  `item_id` int(9),
                  `parent_id` int(9),
                  `path` varchar(255) NOT NULL,
                  `md5` varchar(32) NOT NULL,
                  `added` int NULL DEFAULT NULL,
                  PRIMARY KEY (`id`))
                DEFAULT CHARSET=utf8;");
    module::set_version("folder_sync", 3);
    folder_sync::check_config();
  }

  static function upgrade($version) {
    $db = Database::instance();
    if($version == 1)
    {
      $db->query("ALTER TABLE {folder_sync_entries} ADD COLUMN `added` int NULL DEFAULT NULL AFTER `md5`");
      module::set_version("folder_sync", 2);
    }
    if($version == 2)
    {
      $db->query("ALTER TABLE {folder_sync_entries} DROP COLUMN `task_id`");
      module::set_version("folder_sync", 3);
    }
  }

  static function deactivate() {
    site_status::clear("folder_sync_configuration");
  }
}
