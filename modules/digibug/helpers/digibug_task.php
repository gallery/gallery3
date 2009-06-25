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
class digibug_task_Core {
  static function available_tasks() {
    // Delete extra exif_records
    $expired_request_count = Database::instance()->query(
      "SELECT count(*) as print_requests
         FROM {digibug_proxies}
        WHERE `request_date` <= (CURDATE() - INTERVAL 10 DAY)")->current()->print_requests;

    return array(Task_Definition::factory()
                 ->callback("digibug_task::remove_expired")
                 ->name(t("Remove Digibug print requests"))
                 ->description($expired_request_count
                               ? t2("1 Digibug print request has expired",
                                    "%count Digibug print requests have expired",
                                    $expired_request_count)
                               : t("All print requests are current"))
                 ->severity($expired_request_count ? log::WARNING : log::SUCCESS));
  }

  static function remove_expired($task) {
    $completed = $task->get("completed", 0);
    $expired = ORM::factory("digibug_proxy")
      ->where("request_date <= (CURDATE() - INTERVAL 10 DAY)")
      ->find_all();
    $remaining = $expired->count();

    $start = microtime(true);
    foreach ($expired as $proxy) {
      if (microtime(true) - $start > 1.5) {
        break;
      }
      $proxy->delete();
      $completed++;
      $remaining--;
    }

    if ($completed + $remaining > 0) {
      $task->percent_complete = (int)(100 * $completed / ($completed + $remaining));
    } else {
      $task->percent_complete = 100;
    }

    $task->set("completed", $completed);
    if ($remaining == 0) {
      $task->done = true;
      $task->state = "success";
    }
  }
}
