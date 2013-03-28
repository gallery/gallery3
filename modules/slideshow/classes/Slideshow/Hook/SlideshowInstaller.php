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
class slideshow_installer {
  static function install() {
    module::set_var("slideshow", "max_scale", 0);
  }

  static function upgrade($version) {
    if ($version == 1) {
      module::set_var("slideshow", "max_scale", 0);
      module::set_version("slideshow", $version = 2);
    }
  }

  static function deactivate() {
    site_status::clear("slideshow_needs_rss");
  }

  static function can_activate() {
    $messages = array();
    if (!module::is_active("rss")) {
      $messages["warn"][] = t("The Slideshow module requires the RSS module.");
    }
    return $messages;
  }
}
