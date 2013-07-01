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
  static $max_add_query_terms = 5;

  /**
   * Add more terms to the query by wildcarding the stem value of the first
   * few terms in the query.
   */
  static function add_query_terms($q) {
    $terms = explode(" ", $q, static::$max_add_query_terms);
    for ($i = 0; $i < min(count($terms), static::$max_add_query_terms - 1); $i++) {
      // Don't wildcard quoted or already wildcarded terms
      if ((substr($terms[$i], 0, 1) != '"') && (substr($terms[$i], -1, 1) != "*")) {
        $terms[] = rtrim($terms[$i], "s") . "*";
      }
    }
    return implode(" ", $terms);
  }

  static function search($q, $limit, $offset, $where=array()) {
    return Search::search_within_album($q, Item::root(), $limit, $offset, $where);
  }

  static function search_within_album($q, $album, $limit, $offset, $where=array()) {
    $query = static::search_query_base($q, $album, $where);

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

  static function search_query_base($q, $album, $where=array()) {
    // For *choosing* the found items, we use BOOLEAN MODE to allow special operators (+, -, *,...)
    // For *ordering* the found items, we use NATURAL LANGUAGE MODE to give us a score

    $q_boolean = Database::instance()->escape(Search::add_query_terms($q));
    $q_natural = Database::instance()->escape($q);

    return $album->descendants
      ->viewable()
      ->with("search_record")
      ->select(array(DB::expr("MATCH(`data`) AGAINST ($q_natural)"), "score"))
      ->where(DB::expr("MATCH(`data`)"), "AGAINST", DB::expr("($q_boolean IN BOOLEAN MODE)"))
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

  static function update($item) {
    $data = new ArrayObject();
    $record = $item->search_record;
    if (!$record->loaded()) {
      $record->item_id = $item->id;
    }

    Module::event("item_index_data", $item, $data);
    $record->data = join(" ", (array)$data);
    $record->dirty = 0;
    $record->save();
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

  static function get_position($item, $q, $where=array()) {
    return Search::get_position_within_album($item, $q, Item::root(), $where);
  }

  static function get_position_within_album($item, $q, $album, $where=array()) {
    $items = static::search_query_base($q, $album, $where)
      ->order_by("score", "DESC")
      ->order_by("id", "ASC")  // use id as tie-breaker
      ->find_all();

    foreach ($items as $key => $current_item) {
      if ($item->id == $current_item->id) {
        return $key + 1;  // 1-indexed position
      }
    }

    // We can't find this result in our result set - perhaps we've fallen out of context?  Clear
    // the context and try again.
    Item::clear_display_context_callback();
    HTTP::redirect(Request::current()->uri(true));
  }

  static function get_breadcrumbs($item=null, $q, $album) {
    $params = ($album->is_root() ? array("q" => $q) : array("q" => $q, "album" => $album->id));

    $last_breadcrumbs = array();
    $last_breadcrumbs[] = Breadcrumb::instance($q, URL::site("search") . URL::query($params, false));
    if ($item) {
      $last_breadcrumbs[] = Breadcrumb::instance($item->title, $item->url());
    }

    return Breadcrumb::array_from_item_parents($album, $last_breadcrumbs);
  }
}
