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
    $db = Database::instance();

    // Delete extra exif_records
    $db->query(
      "DELETE {exif_records}.* FROM {exif_records} " .
      "LEFT JOIN {items} ON ({exif_records}.`item_id` = {items}.`id`) " .
      "WHERE {items}.`id` IS NULL");

    // Insert missing exif_records
    $db->query(
      "INSERT INTO {exif_records}(`item_id`) (" .
      " SELECT {items}.`id` FROM {items} " .
      " LEFT JOIN {exif_records} ON ({exif_records}.`item_id` = {items}.`id`) " .
      " WHERE {exif_records}.`id` IS NULL)");

    list ($remaining, $total, $percent) = self::_get_stats();
    return array(Task_Definition::factory()
                 ->callback("exif_task::extract_exif")
                 ->name(t("Extract EXIF data"))
                 ->type("admin")
                 ->description($remaining
                               ? t("%percent% of your photos need to be scanned for EXIF data",
                                   array("percent" => $percent))
                               : t("EXIF data is up-to-date"))
                 ->severity($remaining ? log::WARNING : log::SUCCESS));
  }

  static function extract_exif($task) {
    $completed = $task->get("completed", 0);

    $work = ORM::factory("item")
      ->join("exif_records", "items.id", "exif_records.item_id")
      ->where("exif_records.dirty", 1)
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
    $missing_exif = ORM::factory("exif_record")->where("dirty", 1)->count_all();
    $total_items = ORM::factory("item")->count_all();
    return array($missing_exif, $total_items,
                 round(100 * (($total_items - $missing_exif) / $total_items)));
  }
}
