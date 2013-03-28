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
class image_block_installer {

  static function install() {
    module::set_var("image_block", "image_count", "1");
  }

  static function upgrade($version) {
    $db = Database::instance();
    if ($version == 1) {
      module::set_var("image_block", "image_count", "1");
      module::set_version("image_block", $version = 2);
    }

    // Oops, there was a bug in the installer for version 2 resulting
    // in some folks not getting the image_count variable set.  Bump
    // to version 3 and fix it.
    if ($version == 2) {
      if (module::get_var("image_block", "image_count", 0) === 0) {
        module::set_var("image_block", "image_count", "1");
      }
      module::set_version("image_block", $version = 3);
    }
  }
}
