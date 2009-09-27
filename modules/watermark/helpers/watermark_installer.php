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
class watermark_installer {
  static function install() {
    $db = Database::instance();
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
               DEFAULT CHARSET=utf8;");

    @mkdir(VARPATH . "modules/watermark");
    module::set_version("watermark", 2);
  }

  static function uninstall() {
    Database::instance()->query("DROP TABLE {watermarks}");
    dir::unlink(VARPATH . "modules/watermark");
  }

  static function upgrade($version) {
    $db = Database::instance();
    if ($version == 1) {
      graphics::remove_rules("watermark");
      if ($name = module::get_var("watermark", "name")) {
        foreach (array("thumb", "resize") as $target) {
          graphics::add_rule(
            "watermark", $target, "gallery_graphics::composite",
            array("file" => VARPATH . "modules/watermark/$name",
                  "width" => module::get_var("watermark", "width"),
                  "height" => module::get_var("watermark", "height"),
                  "position" => module::get_var("watermark", "position"),
                  "transparency" => 101 - module::get_var("watermark", "transparency")),
            1000);
        }
      }
      module::set_version("watermark", $version = 2);
    }
  }
}
