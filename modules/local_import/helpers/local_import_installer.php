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
class local_import_installer {
  public static function install() {
    $db = Database::instance();
    $version = module::get_version("local_import");
    if ($version == 0) {
      access::register_permission("local_import", t("Import Local Files"));

      access::allow(user::lookup(2), "view", ORM::factory("item", 1));

      module::set_version("local_import", 1);
      module::set_var("local_import", "authorized_paths", serialize(array()));
    }
  }

  public static function uninstall() {
    access::delete_permission("local_import");
    $module = module::get("local_import");

    $db = Database::instance();
    $db->delete("vars", array("module_name" => $module->name));

    module::delete("local_import");
  }
}
