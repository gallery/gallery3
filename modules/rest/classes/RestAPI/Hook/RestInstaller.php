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
class RestAPI_Hook_RestInstaller {
  static function install() {
    Database::instance()
      ->query(Database::CREATE, "CREATE TABLE {user_access_keys} (
                `id` int(9) NOT NULL auto_increment,
                `user_id` int(9) NOT NULL,
                `access_key` char(32) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY(`access_key`),
                UNIQUE KEY(`user_id`))
              DEFAULT CHARSET=utf8;");
    Module::set_var("rest", "allow_guest_access", false);
  }

  static function upgrade($version) {
    $db = Database::instance();
    if ($version == 1) {
      if (in_array("user_access_tokens", Database::instance()->list_tables())) {
        $db->query(Database::RENAME, "RENAME TABLE {user_access_tokens} TO {user_access_keys}");
      }
      Module::set_version("rest", $version = 2);
    }

    if ($version == 2) {
      Module::set_var("rest", "allow_guest_access", false);
      Module::set_version("rest", $version = 3);
    }

    if ($version == 3) {
      Module::set_var("rest", "allow_jsonp_output", true);
      Module::set_var("rest", "cors_embedding", "none");
      Module::set_var("rest", "approved_domains", "");

      // In Gallery 3.0.x, we didn't prevent the generation of guest access keys at a low level.
      // While it wasn't possible to get Gallery to make one with the core modules, we check
      // here and delete any we see as a defense-in-depth measure.  In Gallery 3.1, we prevent
      // them from ever being created (see RestAPI::access_key()).
      foreach (ORM::factory("UserAccessKey")
               ->where("user_id", "=", Identity::guest()->id)
               ->find_all() as $key) {
        $key->delete();
      }

      Module::set_version("rest", $version = 4);
    }
  }

  static function uninstall() {
    Database::instance()->query(Database::DROP, "DROP TABLE IF EXISTS {user_access_keys}");
  }
}
