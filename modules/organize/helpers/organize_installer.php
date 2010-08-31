<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class organize_installer {
  static function deactivate() {
    site_status::clear("organize_needs_rest");
  }

  static function install() {
    module::set_version("organize", $version = 2);
  }

  static function upgrade($version) {
    if ($version == 1) {
      if (!module::is_active("rest")) {
        site_status::warning(
          t("The Organize module requires the Rest module.  <a href=\"%url\">Activate the Rest module now</a>",
            array("url" => html::mark_clean(url::site("admin/modules")))),
          "organize_needs_rest");
      }

      module::set_version("organize", $version = 2);
    }
  }

  static function can_activate() {
    $messages = array();
    if (!module::is_active("rest")) {
      $messages["warn"][] = t("The Organize module requires the Rest module.");
    }
    return $messages;
  }
}
