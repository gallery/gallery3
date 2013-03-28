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
class info_installer {

  static function install() {
    module::set_var("info", "show_title", 1);
    module::set_var("info", "show_description", 1);
    module::set_var("info", "show_owner", 1);
    module::set_var("info", "show_name", 1);
    module::set_var("info", "show_captured", 1);
  }

  static function upgrade($version) {
    if ($version == 1) {
      module::set_var("info", "show_title", 1);
      module::set_var("info", "show_description", 1);
      module::set_var("info", "show_owner", 1);
      module::set_var("info", "show_name", 1);
      module::set_var("info", "show_captured", 1);
      module::set_version("info", $version = 2);
    }
  }
}
