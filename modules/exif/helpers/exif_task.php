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
class exif_task_Core {
  static function available_tasks() {
    // Delete extra exif_records
    db::build()
      ->delete("exif_records")
      ->where("item_id", "NOT IN",
              db::build()->select("id")->from("items")->where("type", "=", "photo"))
      ->execute();

    list ($remaining, $total, $percent) = exif::stats();
    return array(Task_Definition::factory()
                 ->callback("exif_task::update_index")
                 ->name(t("Extract Exif data"))
                 ->description($remaining
                               ? t2("1 photo needs to be scanned",
                                    "%count (%percent%) of your photos need to be scanned",
                                    $remaining, array("percent" => (100 - $percent)))
                               : t("Exif data is up-to-date"))
                 ->severity($remaining ? log::WARNING : log::SUCCESS));
  }

  static function update_index($task) {
    try {
      $completed = $task->get("completed", 0);

      $start = microtime(true);
      foreach (ORM::factory("item")
               ->join("exif_records", "items.id", "exif_records.item_id", "left")
               ->where("type", "=", "photo")
               ->and_open()
               ->where("exif_records.item_id", "IS", null)
               ->or_where("exif_records.dirty", "=", 1)
               ->close()
               ->find_all(100) as $item) {
        // The query above can take a long time, so start the timer after its done
        // to give ourselves a little time to actually process rows.
        if (!isset($start)) {
          $start = microtime(true);
        }

        exif::extract($item);
        $completed++;

        if (microtime(true) - $start > .75) {
          break;
        }
      }

      list ($remaining, $total, $percent) = exif::stats();
      $task->set("completed", $completed);
      if ($remaining == 0 || !($remaining + $completed)) {
        $task->done = true;
        $task->state = "success";
        site_status::clear("exif_index_out_of_date");
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
      $task->log((string)$e);
    }
  }
}
