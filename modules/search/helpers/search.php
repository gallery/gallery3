<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class search_Core {
  /**
   * Add more terms to the query by wildcarding the stem value of the first
   * few terms in the query.
   */
  static function add_query_terms($q) {
    $MAX_TERMS = 5;
    $terms = explode(" ", $q, $MAX_TERMS);
    for ($i = 0; $i < min(count($terms), $MAX_TERMS - 1); $i++) {
      // Don't wildcard quoted or already wildcarded terms
      if ((substr($terms[$i], 0, 1) != '"') && (substr($terms[$i], -1, 1) != "*")) {
        $terms[] = rtrim($terms[$i], "s") . "*";
      }
    }
    return implode(" ", $terms);
  }

  static function search($q, $limit, $offset) {
    $db = Database::instance();
    $q = $db->escape($q);

    if (!identity::active_user()->admin) {
      foreach (identity::group_ids_for_active_user() as $id) {
        $fields[] = "`view_$id` = TRUE"; // access::ALLOW
      }
      $access_sql = "AND (" . join(" OR ", $fields) . ")";
    } else {
      $access_sql = "";
    }

    $query =
      "SELECT SQL_CALC_FOUND_ROWS {items}.*, " .
      "  MATCH({search_records}.`data`) AGAINST ('$q') AS `score` " .
      "FROM {items} JOIN {search_records} ON ({items}.`id` = {search_records}.`item_id`) " .
      "WHERE MATCH({search_records}.`data`) AGAINST ('$q' IN BOOLEAN MODE) " .
      $access_sql .
      "ORDER BY `score` DESC " .
      "LIMIT $limit OFFSET $offset";
    $data = $db->query($query);
    $count = $db->query("SELECT FOUND_ROWS() as c")->current()->c;

    return array($count, new ORM_Iterator(ORM::factory("item"), $data));
  }

  /**
   * @return string An error message suitable for inclusion in the task log
   */
  static function check_index() {
    list ($remaining) = search::stats();
    if ($remaining) {
      site_status::warning(
        t('Your search index needs to be updated.  <a href="%url" class="g-dialog-link">Fix this now</a>',
          array("url" => html::mark_clean(url::site("admin/maintenance/start/search_task::update_index?csrf=__CSRF__")))),
        "search_index_out_of_date");
    }
  }

  static function update($item) {
    $data = new ArrayObject();
    $record = ORM::factory("search_record")->where("item_id", "=", $item->id)->find();
    if (!$record->loaded()) {
      $record->item_id = $item->id;
    }

    $item = $record->item();
    module::event("item_index_data", $item, $data);
    $record->data = join(" ", (array)$data);
    $record->dirty = 0;
    $record->save();
  }

  static function stats() {
    $remaining = db::build()
      ->from("items")
      ->join("search_records", "items.id", "search_records.item_id", "left")
      ->and_open()
      ->where("search_records.item_id", "IS", null)
      ->or_where("search_records.dirty", "=", 1)
      ->close()
      ->count_records();

    $total = ORM::factory("item")->count_all();
    $percent = round(100 * ($total - $remaining) / $total);

    return array($remaining, $total, $percent);
  }

  static function get_display_context($item, $context) {
    $context_data = $context->data();

    $position = self::get_position($context_data, $item);

    if ($position > 1) {
      list ($count, $result_data) =
         self::search($context_data["query_terms"], 3, $position - 2);
      list ($previous_item, $ignore, $next_item) = $result_data;
    } else {
      $previous_item = null;
      list ($count, $result_data) = self::search($context_data["query_terms"], 1, $position);
      list ($next_item) = $result_data;
    }

    return array("position" =>$position,
                 "previous_item" => $previous_item,
                 "next_item" =>$next_item,
                 "sibling_count" => $count,
                 "parents" => array(item::root(), $context->dynamic_item($context_data["title"],
                                "search?q=" . urlencode($context_data["q"]) . "&show={$item->id}")));
  }

  /**
   * Find the position of the given item in the tag collection.  The resulting
   * value is 1-indexed, so the first child in the album is at position 1.
   *
   * @param String  $query_terms
   * @param Item_Model $item
   * @param array      $where an array of arrays, each compatible with ORM::where()
   */
  public static function get_position($context_data, $item, $where=array()) {
    list($count, $data) = self::search($context_data["query_terms"], $context_data["page_size"],
                                       $context_data["offset"]);

    $page_position = 1;
    foreach ($data as $search_item) {
      if ($item->id == $search_item->id) {
        break;
      }
      $page_position++;
    }

    return  $context_data["offset"] + $page_position;
  }
}
