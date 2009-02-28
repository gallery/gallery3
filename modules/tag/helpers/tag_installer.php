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
class tag_installer {
  static function install() {
    $db = Database::instance();
    $version = module::get_version("tag");
    if ($version == 0) {
      $db->query("CREATE TABLE IF NOT EXISTS {tags} (
                   `id` int(9) NOT NULL auto_increment,
                   `name` varchar(64) NOT NULL,
                   `count` int(10) unsigned NOT NULL DEFAULT 0,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE IF NOT EXISTS {items_tags} (
                   `id` int(9) NOT NULL auto_increment,
                   `item_id` int(9) NOT NULL,
                   `tag_id` int(9) NOT NULL,
                   PRIMARY KEY (`id`),
                   KEY(`tag_id`, `id`),
                   KEY(`item_id`, `id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");
      module::set_version("tag", 1);
    }
  }

  static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS {tags};");
    $db->query("DROP TABLE IF EXISTS {items_tags};");
    module::delete("tag");
  }
}
