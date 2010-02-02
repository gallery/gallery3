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

class scheduler_event_Core {
  /**
   * At this point, the output has been sent to the browser, so we can take some time
   * to run a schedule task.
   */
  static function gallery_shutdown() {
    try {
      $schedule = ORM::factory("schedule")
        ->where("next_run_datetime", "<=", time())
        ->where("busy", "!=", 1)
        ->order_by("next_run_datetime")
        ->find_all(1);

      if ($schedule->count()) {
        $schedule = $schedule->current();
        $schedule->busy = true;
        $schedule->save();

        try {
          if (empty($schedule->task_id)) {
            $task = task::start($schedule->task_callback);
            $schedule->task_id = $task->id;
          }

          $task = task::run($schedule->task_id);

          if ($task->done) {
            $schedule->next_run_datetime += $schedule->interval;
            $schedule->task_id = null;
          }

          $schedule->busy = false;
          $schedule->save();
        } catch (Exception $e) {
          $schedule->busy = false;
          $schedule->save();
          throw $e;
        }
      }
    } catch (Exception $e) {
      Kohana_Log::add("error", (string)$e);
    }
  }
}
