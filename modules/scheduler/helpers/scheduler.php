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
class scheduler_Core {

  static function intervals() {
    static $intervals;
    if (empty($intervals)) {
      $intervals = array("3600" => t("Hourly"), "86400" => t("Daily"),
                         "604800" => t("Weekly"), "2419200" => t("Monthly"));
    }
    return $intervals;
  }

  static function get_form($method, $schedule) {
    if ($method == "define") {
      $title = t("Create a scheduled event");
      $button_text = t("Create");
    } else {
      $title = t("Update a scheduled event");
      $button_text = t("Update");
    }

    $id = empty($schedule->id) ? "" : "/$schedule->id";
    $form = new Forge("admin/schedule/$method{$id}", "", "post",
                      array("id" => "g-{$method}-schedule"));
    $group = $form->group("schedule_group")->label($title);
    $group->input("schedule_name")
      ->label(t("Description"))
      ->id("g-schedule-name")
      ->rules("required|length[0, 128]")
      ->error_messages("required", t("You must provide a description"))
      ->error_messages("length", t("Your description is too long"))
      ->value(!empty($schedule->name) ? $schedule->name : "");

    list ($dow, $display_time) = scheduler::format_time($schedule->next_run_datetime);
    $next = $group->group("run_date")->label(t("Scheduled Date"));
    $next->dropdown("dow")
      ->label(t("Day"))
      ->id("g-schedule-day")
      ->rules("required")
      ->options(array(t("Sunday"), t("Monday"), t("Tuesday"), t("Wednesday"),
                      t("Thursday"), t("Friday"), t("Saturday")))
      ->selected($dow);

    $next->input("time")
      ->label(t("Hour"))
      ->id("g-schedule-time")
      ->rules("required")
      ->error_messages("required", t("You must provide a time"))
      ->error_messages("time_invalid", t("Invalid time"))
      ->callback("scheduler::valid_time")
      ->value($display_time);

    // need to set the top padding to zero on g-define-schedule li.g-error
    $group->dropdown("interval")->label(t("How often"))->id("g-schedule-frequency")
      ->options(scheduler::intervals())
      ->rules("required")
      ->error_messages("required", t("You must provide an interval"))
      ->selected(!empty($schedule->interval) ? $schedule->interval : "2419200");
    $group->hidden("callback")->value($schedule->task_callback);
    $group->submit("")->value($button_text);

    return $form;
  }

  static function format_time($time) {
    $local_time = localtime($time);
    $display_time = str_pad($local_time[2], 2, "0", STR_PAD_LEFT) . ":" .
      str_pad($local_time[1], 2, "0", STR_PAD_LEFT);
    return array($local_time[6], $display_time);
  }

  static function valid_time($field) {
    if (preg_match("/([0-9]{1,2}):([0-9]{2})/", $field->value, $matches)) {
      $hour = (int)$matches[1];
      $minutes = (int)$matches[2];
      if (!(0 <= $hour && $hour<= 23 || 0 <= $minutes && $minutes <= 59)) {
        $field->add_error("time_invalid", 1);
      }
    } else {
      $field->add_error("time_invalid", 1);
    }
  }

  static function get_definitions() {
    $v = "";
    $events = ORM::factory("schedule")
      ->order_by("next_run_datetime", "asc")
      ->find_all();
    if ($events->count()) {
      $v = new View("scheduler_definitions.html");
      $v->schedule_definitions = array();
      foreach ($events as $schedule) {
        $entry[] = $schedule->id;
        $entry[] = $schedule->name;
        $run_date = strftime("%A, %b %e, %Y %H:%M ", $schedule->next_run_datetime);
        $intervals = scheduler::intervals();
        $interval = $intervals[$schedule->interval];
        if (!empty($schedule->task_id)) {
          $status = t("Running");
        } else if ($schedule->next_run_datetime < time()) {
          $status = t("Overdue");
        } else  {
          $status = t("Scheduled");
        }

        $v->schedule_definitions[] = (object)array("id" => $schedule->id,
                                                   "name" => $schedule->name,
                                                   "run_date" => $run_date,
                                                   "interval" => $interval,
                                                   "status" => $status);
      }
    }
    return $v;
  }
}