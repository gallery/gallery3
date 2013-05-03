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
class Search_Hook_SearchTask {
  static function available_tasks() {
    // Delete extra search_records
    DB::delete("search_records")
      ->where("item_id", "NOT IN", DB::select("id")->from("items"))
      ->execute();

    list ($remaining, $total, $percent) = Search::stats();
    return array(TaskDefinition::factory()
                 ->callback("Hook_SearchTask::update_index")
                 ->name(t("Update Search Index"))
                 ->description(
                   $remaining
                   ? t2("1 photo or album needs to be scanned",
                        "%count (%percent%) of your photos and albums need to be scanned",
                        $remaining, array("percent" => (100 - $percent)))
                   : t("Search data is up-to-date"))
                 ->severity($remaining ? GalleryLog::WARNING : GalleryLog::SUCCESS));
  }

  static function update_index($task) {
    try {
      $completed = $task->get_data("completed", 0);

      $start = microtime(true);
      foreach (ORM::factory("Item")
               ->with("search_record")
               ->where("search_record.item_id", "IS", null)
               ->or_where("search_record.dirty", "=", 1)
               ->limit(100)->find_all() as $item) {
        // The query above can take a long time, so start the timer after its done
        // to give ourselves a little time to actually process rows.
        if (!isset($start)) {
          $start = microtime(true);
        }

        Search::update($item);
        $completed++;

        if (microtime(true) - $start > .75) {
          break;
        }
      }

      list ($remaining, $total, $percent) = Search::stats();
      $task->set_data("completed", $completed);
      if ($remaining == 0 || !($remaining + $completed)) {
        $task->done = true;
        $task->state = "success";
        SiteStatus::clear("search_index_out_of_date");
        $task->percent_complete = 100;
      } else {
        $task->percent_complete = round(100 * $completed / ($remaining + $completed));
      }
      $task->status = t2("one record updated, index is %percent% up-to-date",
                         "%count records updated, index is %percent% up-to-date",
                         $completed, array("percent" => $percent));
    } catch (Exception $e) {
      $task->done = true;
      $task->state = "error";
      $task->status = $e->getMessage();
    }
  }
}
