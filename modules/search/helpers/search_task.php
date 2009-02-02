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
class search_task_Core {
  static function available_tasks() {
      // Delete extra search_records
      Database::instance()->query(
        "DELETE `search_records`.* FROM `search_records` " .
        "LEFT JOIN `items` ON (`search_records`.`item_id` = `items`.`id`) " .
        "WHERE `items`.`id` IS NULL");

      // Insert missing search_records
      Database::instance()->query(
        "INSERT INTO `search_records`(`item_id`) (" .
        " SELECT `items`.`id` FROM `items` " .
        " LEFT JOIN `search_records` ON (`search_records`.`item_id` = `items`.`id`) " .
        " WHERE `search_records`.`id` IS NULL)");

    list ($remaining, $total, $percent) = self::_get_stats();
    return array(Task_Definition::factory()
                 ->callback("search_task::update_index")
                 ->name(t("Update Search Index"))
                 ->description($remaining ?
                               t("Search index is %percent% up-to-date",
                                 array("percent" => $percent))
                               : t("Search index is up to date"))
                 ->severity($remaining ? log::WARNING : log::SUCCESS));
  }

  static function update_index($task) {
    $completed = $task->get("completed", 0);

    foreach (ORM::factory("search_record")->where("dirty", 1)->limit(2)->find_all() as $record) {
      search::update_record($record);
      $completed++;
    }
    $task->set("completed", $completed);

    list ($remaining, $total, $percent) = self::_get_stats();
    $task->percent_complete = round(100 * $completed / ($remaining + $completed));

    $task->status = t("%done records records updated, index is %percent% up-to-date",
                      array("done" => $completed, "percent" => $percent));

    if ($remaining == 0) {
      $task->done = true;
      $task->state = "success";
      site_status::clear("search_index_out_of_date");
    }
  }

  private static function _get_stats() {
    $remaining = ORM::factory("search_record")->where("dirty", 1)->count_all();
    $total = ORM::factory("search_record")->count_all();
    $percent = round(100 * ($total - $remaining) / $total);
    return array($remaining, $total, $percent);
  }
}
