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
class g2_import_installer {
  static function install() {
    $db = Database::instance();
    $version = module::get_version("g2_import");
    if ($version == 0) {
      $db->query("CREATE TABLE IF NOT EXISTS {g2_maps} (
                   `id` int(9) NOT NULL auto_increment,
                   `g2_id` int(9) NOT NULL,
                   `g3_id` int(9) NOT NULL,
                 PRIMARY KEY (`id`),
                 KEY (`g2_id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      module::set_version("g2_import", 1);
    }
  }
}
