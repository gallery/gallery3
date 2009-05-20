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

    if (!user::active()->admin) {
      foreach (user::group_ids() as $id) {
        $fields[] = "`view_$id` = " . access::ALLOW;
      }
      $access_sql = "AND (" . join(" AND ", $fields) . ")";
    } else {
      $access_sql = "";
    }

    // Count the total number of rows.  We can't do this with our regular query because of the
    // limit statement.  It's possible that if we get rid of the limit (but keep the offset) on
    // the 2nd query and combine the two, it might be faster than making 2 separate queries.
    $count_query = "SELECT COUNT(*) AS C " .
      "FROM {items} JOIN {search_records} ON ({items}.`id` = {search_records}.`item_id`) " .
      "WHERE MATCH({search_records}.`data`) AGAINST ('$q' IN BOOLEAN MODE) " .
      $access_sql;
    $count = $db->query($count_query)->current()->C;

    $query = "SELECT {items}.*, MATCH({search_records}.`data`) AGAINST ('$q') AS `score` " .
      "FROM {items} JOIN {search_records} ON ({items}.`id` = {search_records}.`item_id`) " .
      "WHERE MATCH({search_records}.`data`) AGAINST ('$q' IN BOOLEAN MODE) " .
      $access_sql .
      "ORDER BY `score` DESC " .
      "LIMIT $offset, $limit";

    return array($count, new ORM_Iterator(ORM::factory("item"), $db->query($query)));
  }

  static function check_index() {
    list ($remaining) = search::stats();
    if ($remaining) {
      site_status::warning(
        t('Your search index needs to be updated.  <a href="%url" class="gDialogLink">Fix this now</a>',
          array("url" => url::site("admin/maintenance/start/search_task::update_index?csrf=__CSRF__"))),
        "search_index_out_of_date");
    }
  }

  static function update($item) {
    $data = array();
    $record = ORM::factory("search_record")->where("item_id", $item->id)->find();
    if (!$record->loaded) {
      $record->item_id = $item->id;
    }

    foreach (module::installed() as $module_name => $module_info) {
      $class_name = "{$module_name}_search";
      if (method_exists($class_name, "item_index_data")) {
        $data[] = call_user_func(array($class_name, "item_index_data"), $record->item());
      }
    }
    $record->data = join(" ", $data);
    $record->dirty = 0;
    $record->save();
  }

  static function stats() {
    $remaining = Database::instance()
      ->select("items.id")
      ->from("items")
      ->join("search_records", "items.id", "search_records.item_id", "left")
      ->where("search_records.item_id", null)
      ->orwhere("search_records.dirty", 1)
      ->get()
      ->count();
    $total = ORM::factory("item")->count_all();
    $percent = round(100 * ($total - $remaining) / $total);
    return array($remaining, $total, $percent);
  }
}
