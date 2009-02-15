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
class core_task_Core {
  static function available_tasks() {
    $dirty_count = graphics::find_dirty_images_query()->count();
    return array(Task_Definition::factory()
                 ->callback("core_task::rebuild_dirty_images")
                 ->name(t("Rebuild Images"))
                 ->description($dirty_count ?
                               t2("You have one out of date photo",
                                  "You have %count out of date photos",
                                  $dirty_count)
                               : t("All your photos are up to date"))
                 ->severity($dirty_count ? log::WARNING : log::SUCCESS));
  }

  /**
   * Task that rebuilds all dirty images.
   * @param Task_Model the task
   */
  static function rebuild_dirty_images($task) {
    $result = graphics::find_dirty_images_query();
    $remaining = $result->count();
    $completed = $task->get("completed", 0);

    $i = 0;
    foreach ($result as $row) {
      $item = ORM::factory("item", $row->id);
      if ($item->loaded) {
        graphics::generate($item);
      }

      $completed++;
      $remaining--;

      if (++$i == 2) {
        break;
      }
    }

    $task->status = t2("Updated: 1 image. Total: %total_count.",
                       "Updated: %count images. Total: %total_count.",
                       $completed,
                       array("total_count" => ($remaining + $completed)));

    if ($completed + $remaining > 0) {
      $task->percent_complete = (int)(100 * $completed / ($completed + $remaining));
    } else {
      $task->percent_complete = 100;
    }

    $task->set("completed", $completed);
    if ($remaining == 0) {
      $task->done = true;
      $task->state = "success";
      site_status::clear("graphics_dirty");
    }
  }
}