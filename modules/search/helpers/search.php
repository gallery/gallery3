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
class search_Core {
  static function search($q, $limit, $offset) {
    $db = Database::instance();
    $q = $db->escape_str($q);

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

    return array($count, new ORM_Iterator(ORM::factory("item"), $db->query($query)));
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
    $record = ORM::factory("search_record")->where("item_id", $item->id)->find();
    if (!$record->loaded) {
      $record->item_id = $item->id;
    }

    module::event("item_index_data", $record->item(), $data);
    $record->data = join(" ", (array)$data);
    $record->dirty = 0;
    $record->save();
  }

  static function stats() {
    $remaining = Database::instance()
      ->select("items.id")
      ->from("items")
      ->join("search_records", "items.id", "search_records.item_id", "left")
      ->open_paren()
      ->where("search_records.item_id", null)
      ->orwhere("search_records.dirty", 1)
      ->close_paren()
      ->get()
      ->count();

    $total = ORM::factory("item")->count_all();
    $percent = round(100 * ($total - $remaining) / $total);

    return array($remaining, $total, $percent);
  }
}
