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
    $version = module::get_version("digibug");
    if ($version == 0) {
      Database::instance()
        ->query("CREATE TABLE {print_proxy} (
                   `id` int(9) NOT NULL auto_increment,
                   `proxy_id` char(55) NOT NULL,
                   `item_id` int(9),
                   PRIMARY KEY (`id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      module::set_var("digibug", "basic_company_id", "3153");
      module::set_var("digibug", "basic_event_id", "8491");
      module::set_var("digibug", "mode", "basic");

      module::set_version("digibug", 1);
    }
  }

  static function uninstall() {
    Database::instance()->query("DROP TABLE IF EXISTS {print_proxy}");
    module::delete("digibug");
  }
}
