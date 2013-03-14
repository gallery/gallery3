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
    return search::search_within_album($q, item::root(), $limit, $offset);
  }

  static function search_within_album($q, $album, $limit, $offset) {
    $db = Database::instance();

    $query = self::_build_query_base($q, $album) .
      " ORDER BY `score` DESC" .
      " LIMIT $limit OFFSET " . (int)$offset;

    $data = $db->query($query);
    $count = $db->query("SELECT FOUND_ROWS() as c")->current()->c;

    return array($count, new ORM_Iterator(ORM::factory("item"), $data));
  }

  private static function _build_query_base($q, $album, $where=array()) {
    $db = Database::instance();
    $q = $db->escape($q);

    if (!identity::active_user()->admin) {
      foreach (identity::group_ids_for_active_user() as $id) {
        $fields[] = "`view_$id` = TRUE"; // access::ALLOW
      }
      $access_sql = " AND (" . join(" OR ", $fields) . ")";
    } else {
      $access_sql = "";
    }

    if ($album->id == item::root()->id) {
      $album_sql = "";
    } else {
      $album_sql =
        " AND {items}.left_ptr > " . $db->escape($album->left_ptr) .
        " AND {items}.right_ptr <= " . $db->escape($album->right_ptr);
    }

    return
      "SELECT SQL_CALC_FOUND_ROWS {items}.*, " .
      "  MATCH({search_records}.`data`) AGAINST ('$q') AS `score` " .
      "FROM {items} JOIN {search_records} ON ({items}.`id` = {search_records}.`item_id`) " .
      "WHERE MATCH({search_records}.`data`) AGAINST ('$q' IN BOOLEAN MODE)" .
      $album_sql .
      (empty($where) ? "" : " AND " . join(" AND ", $where)) .
      $access_sql;
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

  static function get_position($item, $q) {
    return search::get_position_within_album($item, $q, item::root());
  }

  static function get_position_within_album($item, $q, $album) {
    $page_size = module::get_var("gallery", "page_size", 9);
    $query = self::_build_query_base($q, $album, array("{items}.id = " . $item->id)) .
      " ORDER BY `score` DESC";
    $db = Database::instance();

    // Truncate the score by two decimal places as this resolves the issues
    // that arise due to inexact numeric conversions.
    $current = $db->query($query)->current();
    if (!$current) {
      // We can't find this result in our result set - perhaps we've fallen out of context?  Clear
      // the context and try again.
      item::clear_display_context_callback();
      url::redirect(url::current());
    }
    $score = $current->score;
    if (strlen($score) > 7) {
      $score = substr($score, 0, strlen($score) - 2);
    }

    // Redo the query but only look for results greater than or equal to our current location
    // then seek backwards until we find our item.
    $data = $db->query(self::_build_query_base($q, $album) . " HAVING `score` >= " . $score .
                       " ORDER BY `score` DESC");
    $data->seek($data->count() - 1);

    while ($data->get("id") != $item->id && $data->prev()->valid()) {
    }

    return $data->key() + 1;
  }
}
