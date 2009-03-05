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
class server_add_installer {
  static function install() {
    $db = Database::instance();
    $version = module::get_version("server_add");
    if ($version == 0) {
      access::register_permission("server_add", t("Add files from server"));

      access::allow(user::lookup(2), "view", ORM::factory("item", 1));

      module::set_version("server_add", 1);
      module::set_var("server_add", "authorized_paths", serialize(array()));
      message::warning(
        t("You have no upload directories, <a href='%url'>Configure them now</a> " .
          "to configure one", array("url" => url::site("/admin/server_add"))));
    }
  }

  static function uninstall() {
    access::delete_permission("server_add");
    $module = module::get("server_add");

    // @todo remove this after the next alpha
    module::delete("local_import");
    module::delete("server_add");
  }
}
