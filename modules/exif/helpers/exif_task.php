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
class exif_task_Core {
  static function available_tasks() {
    // Delete extra exif_records
    Database::instance()->query(
      "DELETE FROM {exif_records} " .
      "WHERE {exif_records}.`item_id` NOT IN " .
      "(SELECT `id` FROM {items} WHERE {items}.`type` = 'photo')");

    list ($remaining, $total, $percent) = self::_get_stats();
    return array(Task_Definition::factory()
                 ->callback("exif_task::extract_exif")
                 ->name(t("Extract EXIF data"))
                 ->description($remaining
                               ? t2("1 photo needs to be scanned",
                                    "%count (%percent%) of your photos need to be scanned",
                                    $remaining, array("percent" => (100 - $percent)))
                               : t("EXIF data is up-to-date"))
                 ->severity($remaining ? log::WARNING : log::SUCCESS));
  }

  static function extract_exif($task) {
    $completed = $task->get("completed", 0);

    $start = microtime(true);
    foreach (ORM::factory("item")
             ->join("exif_records", "items.id", "exif_records.item_id", "left")
             ->where("type", "photo")
             ->open_paren()
             ->where("exif_records.item_id", null)
             ->orwhere("exif_records.dirty", 1)
             ->close_paren()
             ->find_all() as $item) {
      if (microtime(true) - $start > 1.5) {
        break;
      }

      $completed++;
      exif::extract($item);
    }

    list ($remaining, $total, $percent) = self::_get_stats();
    if ($remaining + $completed) {
      $task->percent_complete = round(100 * $completed / ($remaining + $completed));
      $task->status = t2("one record updated, index is %percent% up-to-date",
                         "%count records updated, index is %percent% up-to-date",
                         $completed, array("percent" => $percent));
    } else {
      $task->percent_complete = 100;
    }

    $task->set("completed", $completed);
    if ($remaining == 0) {
      $task->done = true;
      $task->state = "success";
    }
  }

  private static function _get_stats() {
    $missing_exif = Database::instance()
      ->select("items.id")
      ->from("items")
      ->join("exif_records", "items.id", "exif_records.item_id", "left")
      ->where("type", "photo")
      ->open_paren()
      ->where("exif_records.item_id", null)
      ->orwhere("exif_records.dirty", 1)
      ->close_paren()
      ->get()
      ->count();

    $total_items = ORM::factory("item")->where("type", "photo")->count_all();
    if (!$total_items) {
      return array(0, 0, 0);
    }
    return array($missing_exif, $total_items,
                 round(100 * (($total_items - $missing_exif) / $total_items)));
  }
}
