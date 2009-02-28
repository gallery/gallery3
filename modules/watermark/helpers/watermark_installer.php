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
class watermark_installer {
  static function install() {
    $db = Database::instance();
    $version = module::get_version("watermark");
    if ($version == 0) {
      $db->query("CREATE TABLE IF NOT EXISTS {watermarks} (
                   `id` int(9) NOT NULL auto_increment,
                   `name` varchar(32) NOT NULL,
                   `width` int(9) NOT NULL,
                   `height` int(9) NOT NULL,
                   `active` boolean default 0,
                   `position` boolean default 0,
                   `mime_type` varchar(64) default NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      @mkdir(VARPATH . "modules/watermark");
      module::set_version("watermark", 1);
    }
  }

  static function uninstall() {
    graphics::remove_rules("watermark");
    module::delete("watermark");
    Database::instance()->query("DROP TABLE {watermarks}");
    dir::unlink(VARPATH . "modules/watermark");
  }
}
