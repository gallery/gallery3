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
class exif_task_Core {
  static function available_tasks() {
    list ($remaining, $total, $percent) = self::_get_stats();
    return array(Task_Definition::factory()
                 ->callback("exif_task::extract_exif")
                 ->name(t("Extract EXIF data"))
                 ->description($remaining ?
                               sprintf(t("EXIF data is available for %d%% of the images"), $percent)
                               : t("EXIF data is up-to-date"))
                 ->severity($remaining ? log::WARNING : log::SUCCESS));
  }

  static function extract_exif($task) {
    $completed = $task->get("completed", 0);

    $work = ORM::factory("item")
      ->join("exif_keys", "items.id", "item_id", "LEFT")
      ->where("items.type", "photo")
      ->where("exif_keys.id", null)
      ->find();
    exif::extract($work);
    $completed++;

    $task->set("completed", $completed);

    list ($remaining, $total, $percent) = self::_get_stats();
    $task->percent_complete = round(100 * $completed / ($remaining + $completed));

    $task->status = t("%done records records updated, index is %percent% up-to-date",
                      array("done" => $completed, "percent" => $percent));

    if ($remaining == 0) {
      $task->done = true;
      $task->state = "success";
    }
  }

  private static function _get_stats() {
    $exif_count = ORM::factory("exif_key")
      ->select("DISTINCT item_id")
      ->find_all()
      ->count();

    $items_count = ORM::factory("item")
      ->where("type", "photo")
      ->count_all();
    return array($exif_count -  $items_count, $items_count,
                 round(100 * ($exif_count / $items_count)));
  }
}
