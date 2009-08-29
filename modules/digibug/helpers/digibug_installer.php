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
class digibug_installer {
  static function install() {
    Database::instance()
      ->query("CREATE TABLE {digibug_proxies} (
                `id` int(9) NOT NULL AUTO_INCREMENT,
                `uuid` char(32) NOT NULL,
                `request_date` TIMESTAMP NOT NULL DEFAULT current_timestamp,
                `item_id` int(9) NOT NULL,
               PRIMARY KEY (`id`))
               DEFAULT CHARSET=utf8;");

    module::set_var("digibug", "company_id", "3153");
    module::set_var("digibug", "event_id", "8491");
    module::set_version("digibug", 2);
  }

  static function upgrade($version) {
    if ($version == 1) {
      module::clear_var("digibug", "default_company_id");
      module::clear_var("digibug", "default_event_id");
      module::clear_var("digibug", "basic_default_company_id");
      module::clear_var("digibug", "basic_event_id");
      module::set_var("digibug", "company_id", "3153");
      module::set_var("digibug", "event_id", "8491");
      module::set_version("digibug", $version = 2);
    }
  }

  static function uninstall() {
    Database::instance()->query("DROP TABLE IF EXISTS {digibug_proxies}");
    module::delete("digibug");
  }
}
