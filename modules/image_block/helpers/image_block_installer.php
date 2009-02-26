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
class image_block_installer {
  static function install() {
    $db = Database::instance();
    $version = module::get_version("image_block");
    if ($version == 0) {
      $db = Database::instance();
      
      $db->query("ALTER TABLE `items` ADD `rand_key` FLOAT DEFAULT NULL");
      $db->query("UPDATE `items` SET `rand_key` = RAND()");
      $db->query("CREATE INDEX `random_index` ON `items` (rand_key DESC)");

      module::set_version("image_block", 1);
    }
  }

  static function uninstall() {
    $db = Database::instance();
    // Dropping the column should drop the index as well.
    $db->query("ALTER TABLE `items` DROP `rand_key`");

    module::delete("image_block");
  }
}
