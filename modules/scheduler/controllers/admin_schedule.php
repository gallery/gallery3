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
class Admin_Schedule_Controller extends Admin_Controller {
  public function form_add($task_callback) {
    access::verify_csrf();

    $schedule = ORM::factory("schedule");
    $schedule->task_callback = $task_callback;
    $schedule->next_run_datetime = time();
    $v = new View("admin_schedule.html");
    $v->form = scheduler::get_form("define", $schedule);
    $v->method = "define";
    print $v;
  }

  public function update_form($id) {
    access::verify_csrf();


    $schedule = ORM::factory("schedule", $id);
    $v = new View("admin_schedule.html");
    $v->form = scheduler::get_form("update", $schedule);
    $v->method = "update";
    print $v;
  }

  public function remove_form($id) {
    access::verify_csrf();

    $schedule = ORM::factory("schedule", $id);

    $v = new View("admin_schedule_confirm.html");
    $v->name = $schedule->name;
    $v->form = new Forge("admin/schedule/remove/{$id}", "", "post",
                         array("id" => "g-remove-schedule"));
    $group = $v->form->group("remove");
    $group->submit("")->value(t("Continue"));
    print $v;
  }

  public function remove($id) {
    access::verify_csrf();
    $schedule = ORM::factory("schedule", $id);
    $schedule->delete();

    message::success(t("Removed scheduled task: %name", array("name" => $schedule->name)));
    print json_encode(array("result" => "success", "reload" => 1));
  }

  public function define() {
    $this->_handle_request("define");
   }

  public function update($id=null) {
    $this->_handle_request("update", $id);
   }

  private function _handle_request($method, $id=null) {
    $schedule = ORM::factory("schedule", $id);
    $form = scheduler::get_form($method, $schedule);
    $valid = $form->validate();
    if ($valid) {
      $schedule->name = $form->schedule_group->schedule_name->value;
      $schedule->interval = $form->schedule_group->interval->value;
      $schedule->next_run_datetime =
        $this->_start_date($form->schedule_group->run_date->dow->selected,
                           $form->schedule_group->run_date->time->value);
      $schedule->task_callback = $form->schedule_group->callback->value;
      $schedule->save();
      if ($method == "define") {
        message::success(t("Added scheduled task: %name", array("name" => $schedule->name)));
      } else {
        message::success(t("Updated scheduled task: %name", array("name" => $schedule->name)));
      }
      print json_encode(array("result" => "success", "reload" => 1));
    } else {
      print json_encode(array("result" => "error", "form" => (string) $form));
    }
  }

  private function _start_date($dow, $time) {
    list ($hour, $minutes) = explode(":", $time);
    $local_time = localtime();
    $days = ($dow < $local_time[6] ? 7 : 0) + $dow - $local_time[6];
    return
      mktime($hour, $minutes, 0, $local_time[4] + 1, $local_time[3] + $days, 1900 + $local_time[5]);
  }
}
