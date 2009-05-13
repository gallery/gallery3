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
class server_add_Core {
  static function check_config($paths=null) {
    if ($paths === null) {
      $paths = unserialize(module::get_var("server_add", "authorized_paths"));
    }
    if (empty($paths)) {
      site_status::warning(
        t("Server Add needs configuration. <a href=\"%url\">Configure it now!</a>",
          array("url" => url::site("admin/server_add"))),
        "server_add_configuration");
    } else {
      site_status::clear("server_add_configuration");
    }
  }
}
