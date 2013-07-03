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
class Search_Hook_SearchEvent {
  /**
   * Setup the relationship between Model_Item and Model_SearchRecord.
   */
  static function model_relationships($relationships) {
    $relationships["item"]["has_one"]["search_record"] = array();
    $relationships["search_record"]["belongs_to"]["item"] = array();
  }

  static function item_created($item) {
    Search::update($item);
  }

  static function item_updated($original, $new) {
    Search::update($new);
  }

  static function item_deleted($item) {
    $item->search_record->delete();
  }

  static function item_related_update($item) {
    Search::update($item);
  }

  static function admin_menu($menu, $theme) {
    $menu->get("settings_menu")
      ->append(Menu::factory("link")
               ->id("search")
               ->label(t("Search"))
               ->url(url::site("admin/search")));
  }

  /**
   * Modify the search terms based on the "wildcard_mode" and "short_search_fix" module vars.
   */
  static function search_terms($terms, $type) {
    $wildcard_mode = Module::get_var("search", "wildcard_mode", "append_stem");
    $prefix = Module::get_var("search", "short_search_fix", false) ?
              Module::get_var("search", "short_search_prefix", "1Z") : "";

    $quoted = false;
    for($i = 1; $i < count($terms); $i += 2) {
      $quoted = (strpos($terms[$i - 1], '"') === false) ? $quoted : !$quoted;

      // Skip empty terms (exact match since "0" isn't empty).
      if ((string)$terms[$i] === "") {
        continue;
      }

      switch ($type) {
      case "boolean":
        // Add wildcards to terms that are neither quoted nor already wildcarded.
        if (!$quoted && (substr($terms[$i + 1], 0, 1) != "*")) {
          switch ($wildcard_mode) {
          case "append_stem":
            $singular = str_split(Inflector::singular($terms[$i]));
            $plural   = str_split(Inflector::plural($terms[$i]));
            $terms[$i] = implode("", array_intersect_assoc($singular, $plural));
          case "append":
            $terms[$i + 1] = "*" . $terms[$i + 1];
          case "none":
            break;
          default:
            throw new Gallery_Exception("Invalid search wildcard_mode setting: $wildcard_mode");
          }
        }

      case "natural_language":
      case "index":
        // Add the prefix to the delimiter of all terms.
        $terms[$i - 1] .= $prefix;
      }
    }
  }
}
