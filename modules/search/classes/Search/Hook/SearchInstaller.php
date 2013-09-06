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
class Search_Hook_SearchInstaller {
  static function install() {
    // For MySQL <5.6, full-text searches require the MyISAM engine (as opposed to InnoDB).
    $db = Database::instance();
    $db->query(Database::CREATE, "CREATE TABLE {search_records} (
                 `id` int(9) NOT NULL auto_increment,
                 `item_id` int(9),
                 `dirty` boolean default 1,
                 `data` LONGTEXT default NULL,
                 PRIMARY KEY (`id`),
                 KEY(`item_id`),
                 FULLTEXT INDEX (`data`))
               ENGINE=MyISAM
               DEFAULT CHARSET=utf8;");

    Module::set_var("search", "item_types", "all");
    Module::set_var("search", "wildcard_mode", "append_stem");
    Module::set_var("search", "short_search_fix", false);
    Module::set_var("search", "short_search_prefix", "1Z");
  }

  static function activate() {
    // Update the root item.  This is a quick hack because the search module is activated as part
    // of the official install, so this way we don't start off with a "your index is out of date"
    // banner.
    Search::update(Item::root());
    Search::check_index();
  }

  static function upgrade($version) {
    if ($version == 1) {
      // In v2, we added some additional module variables for wildcards and short search fixes.
      Module::set_var("search", "item_types", "all");
      Module::set_var("search", "wildcard_mode", "append_stem");
      Module::set_var("search", "short_search_fix", false);
      Module::set_var("search", "short_search_prefix", "1Z");
      Module::set_version("search", $version = 2);
    }
  }

  static function deactivate() {
    SiteStatus::clear("search_index_out_of_date");
  }

  static function uninstall() {
    Database::instance()->query(Database::DROP, "DROP TABLE {search_records}");
    Module::clear_vars("search");
  }
}
