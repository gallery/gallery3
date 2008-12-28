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
class Admin_Maintenance_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");

    $available_tasks = array(
      new ArrayObject(
        array("name" => "rebuild_images",
              "description" => _("Rebuild out of date thumbnails and resizes")),
        ArrayObject::ARRAY_AS_PROPS));

    $view->content = new View("admin_maintenance.html");
    $view->content->available_tasks = $available_tasks;
    $view->content->running_tasks = ORM::factory("task")->find_all();
    print $view;
  }

  public function start($task_name) {
    $task = ORM::factory("task");
    $task->name = $task_name;
    $task->percent_complete = 0;
    $task->status = "";
    $task->context = serialize(array());
    $task->save();

    $view = new View("admin_maintenance_task.html");
    $view->task = $task;
    print $view;
  }

  public function run($task_id) {
    $task = ORM::factory("task", $task_id);
    if (!$task->loaded) {
      throw new Exception("@todo MISSING_TASK");
    }

    switch($task->name) {
    case "rebuild_images":
      graphics::rebuild_dirty_images($task);
    }

    $task->save();

    print json_encode(
      array("status" => "success",
            "task" => $task->as_array()));
  }
}
