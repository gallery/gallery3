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
class search_installer {
  static function install() {
    $version = module::get_version("search");
    $db = Database::instance();
    if ($version == 0) {
      $db->query("CREATE TABLE `search_records` (
                   `id` int(9) NOT NULL auto_increment,
                   `item_id` int(9),
                   `dirty` boolean default 1,
                   `data` LONGTEXT default NULL,
                   PRIMARY KEY (`id`),
                   FULLTEXT INDEX (`data`))
                 ENGINE=MyISAM DEFAULT CHARSET=utf8;");

      // populate the index with dirty records
      $db->query("insert into `search_records` (`item_id`) SELECT `id` FROM `items`");
      module::set_version("search", 1);

      if (ORM::factory("search_record")->count_all() < 10) {
        foreach (ORM::factory("search_record")->where("dirty", 1)->find_all() as $record) {
          search::update_record($record);
        }
      } else {
        search::check_index();
      }
    }
  }

  static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE `search_records`");
    site_status::clear("search_index_out_of_date");
    module::delete("search");
  }
}
