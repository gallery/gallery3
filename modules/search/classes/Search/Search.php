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
class Search_Search {
  // List of MySQL full-text search delimiters.  This is a superset of the BOOLEAN MODE operators,
  // given by "ft_boolean_syntax", and some additional word separators.
  // @see  http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_ft_boolean_syntax
  // @see  http://dev.mysql.com/doc/refman/5.0/en/fulltext-natural-language.html
  static $delimiters = '+ -><()~*:"&|,.;';

  static function search($q, $limit, $offset, $where=array()) {
    return Search::search_within_album($q, Item::root(), $limit, $offset, $where);
  }

  static function search_within_album($q, $album, $limit, $offset, $where=array()) {
    $query = static::get_search_query($q, $album, $where);

    $count = $query
      ->reset(false)
      ->count_all();

    $items = $query
      ->order_by("score", "DESC")
      ->order_by("id", "ASC")  // use id as tie-breaker
      ->limit($limit)
      ->offset($offset)
      ->find_all();

    return array($count, $items);
  }

  /**
   * Build a search query.  This takes a search string and album, runs the "search_terms" events
   * to modify the terms as needed, and returns an ORM query.
   * @see  http://dev.mysql.com/doc/refman/5.0/en/fulltext-search.html
   */
  static function get_search_query($q, $album, $where=array()) {
    // For *choosing* the found items, we use BOOLEAN MODE to allow special operators (+, -, *,...)
    // For *ordering* the found items, we use NATURAL LANGUAGE MODE to give us a score

    // Run the "search_terms" events to modify the search terms as needed.
    $q_boolean = new ArrayObject(Search::explode_fulltext_query($q));
    $q_natural = clone $q_boolean;
    Module::event("search_terms", $q_boolean, "boolean");
    Module::event("search_terms", $q_natural, "natural_language");
    $q_boolean = Database::instance()->escape(implode("", (array)$q_boolean));
    $q_natural = Database::instance()->escape(implode("", (array)$q_natural));

    switch ($item_types = Module::get_var("search", "item_types", "all")) {
    case "no_albums":
      $where[] = array("type", "<>", "album");
      break;
    case "photos_only":
      $where[] = array("type", "=", "photo");
      break;
    case "all":
      break;
    default:
      throw new Gallery_Exception("Invalid search item_types setting: $item_types");
    }

    // Build the query.
    return $album->descendants
      ->with("search_record")
      ->select(array(DB::expr("MATCH(`data`) AGAINST ($q_natural)"), "score"))
      ->where(DB::expr("MATCH(`data`)"), "AGAINST", DB::expr("($q_boolean IN BOOLEAN MODE)"))
      ->viewable()
      ->merge_where($where);
  }

  /**
   * @return string An error message suitable for inclusion in the task log
   */
  static function check_index() {
    list ($remaining) = Search::stats();
    if ($remaining) {
      SiteStatus::warning(
        t('Your search index needs to be updated.  <a href="%url" class="g-dialog-link">Fix this now</a>',
          array("url" => HTML::mark_clean(URL::site("admin/maintenance/start/Hook_SearchTask::update_index?csrf=__CSRF__")))),
        "search_index_out_of_date");
    }
  }

  /**
   * Update an item's search record.  This runs the "item_index_data" event to gather the search
   * data, the "search_terms" event to modify the data as needed, and then builds the record.
   */
  static function update($item) {
    // Get data using "item_index_data" event.
    $data = new ArrayObject();
    Module::event("item_index_data", $item, $data);
    $data = implode(" ", (array)$data);

    // Modify/reformat data using "search_terms" event.
    $data = new ArrayObject(Search::explode_fulltext_query($data));
    Module::event("search_terms", $data, "index");
    $data = implode("", (array)$data);

    // Create/update search record.
    $record = $item->search_record;
    if (!$record->loaded()) {
      $record->item_id = $item->id;
    }

    $record->data = $data;
    $record->dirty = 0;
    $record->save();
  }

  /**
   * Mark all search records as dirty.
   */
  static function mark_dirty() {
    DB::update("search_records")
      ->set(array("dirty" => 1))
      ->execute();

    Search::check_index();
  }

  static function stats() {
    $remaining = ORM::factory("Item")
      ->with("search_record")
      ->where("search_record.item_id", "IS", null)
      ->or_where("search_record.dirty", "=", 1)
      ->count_all();

    $total = ORM::factory("Item")->count_all();
    $percent = round(100 * ($total - $remaining) / $total);

    return array($remaining, $total, $percent);
  }

  /**
   * Build the breadcrumbs for a search query.
   */
  static function get_breadcrumbs($item=null, $q, $album) {
    $params = array("q" => $q);
    if (!$album->is_root()) {
      $params["album"] = $album->id;
    }
    if ($item) {
      $params["show"] = $item->id;
    }

    $breadcrumbs = Breadcrumb::array_from_item_parents($album);
    $breadcrumbs[] = Breadcrumb::factory(t("Search: %q", array("q" => $q)),
                                         URL::site("search") . URL::query($params, false));
    if ($item) {
      $breadcrumbs[] = Breadcrumb::factory($item->title, $item->url());
    }

    return $breadcrumbs;
  }

  /**
   * Explode a fulltext query string into its delimiters and terms.  This returns an array like:
   *   array([delims], [term], [delims], .... [term], [delims])
   * which always begins and ends with delimiters (which may be an empty string).  Example:
   *   "foo bar* +baz,bah " --> array("", "foo", " ", "bar", "* +", "baz", ",", "bah", " ")
   * The inverse of this function is simply implode("", $parts).
   */
  static function explode_fulltext_query($q) {
    $delims = str_split(static::$delimiters);

    // Pad the search query.  This padding never appears in the results.
    $q = " " . $q . " X";

    // Loop through each character in $q and explode into $parts.
    $start = 0;
    $delim = true;
    $parts = array();
    for ($i = 0; $i <= strlen($q); $i++) {
      if ($delim != in_array(substr($q, $i, 1), $delims)) {
        // Boundary between delims and term found - add to $parts, reset $start, and toggle $delim.
        $parts[] = substr($q, $start, $i - $start);
        $start = $i;
        $delim = !$delim;
      }
    }

    // Remove the padding (one space at start and end - "X" never appears).
    $end = count($parts) - 1;
    $parts[0]    = (string)substr($parts[0], 1);
    $parts[$end] = (string)substr($parts[$end], 0, -1);

    return $parts;
  }
}
