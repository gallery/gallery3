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
  /**
   * Show a list of all available, running and finished tasks.
   */
  public function index() {
    $query = Database::instance()->query(
      "UPDATE {tasks} SET `state` = 'stalled' " .
      "WHERE done = 0 " .
      "AND   state <> 'stalled' " .
      "AND   unix_timestamp(now()) - updated > 15");
    $stalled_count = $query->count();
    if ($stalled_count) {
      log::warning("tasks",
                   t2("One task is stalled",
                      "%count tasks are stalled",
                      $stalled_count),
                   t('<a href="%url">view</a>',
                     array("url" => url::site("admin/maintenance"))));
    }

    $view = new Admin_View("admin.html");
    $view->content = new View("admin_maintenance.html");
    $view->content->task_definitions = task::get_definitions();
    $view->content->running_tasks = ORM::factory("task")
      ->where("done", 0)->orderby("updated", "DESC")->find_all();
    $view->content->finished_tasks = ORM::factory("task")
      ->where("done", 1)->orderby("updated", "DESC")->find_all();
    print $view;
  }

  /**
   * Start a new task
   * @param string $task_callback
   */
  public function start($task_callback) {
    access::verify_csrf();

    $tasks = task::get_definitions();

    $task = task::create($tasks[$task_callback], array());

    $view = new View("admin_maintenance_task.html");
    $view->task = $task;

    log::info("tasks", t("Task %task_name started (task id %task_id)",
                         array("task_name" => $task->name, "task_id" => $task->id)),
              html::anchor(url::site("admin/maintenance"), t("maintenance")));
    print $view;
  }

  /**
   * Resume a stalled task
   * @param string $task_id
   */
  public function resume($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);
    if (!$task->loaded) {
      throw new Exception("@todo MISSING_TASK");
    }
    $view = new View("admin_maintenance_task.html");
    $view->task = $task;

    log::info("tasks", t("Task %task_name resumed (task id %task_id)",
                         array("task_name" => $task->name, "task_id" => $task->id)),
              html::anchor(url::site("admin/maintenance"), t("maintenance")));
    print $view;
  }

  /**
   * Cancel a task.
   * @param string $task_id
   */
  public function cancel($task_id) {
    access::verify_csrf();

    task::cancel($task_id);

    message::success(t("Task cancelled"));
    url::redirect("admin/maintenance");
  }

  public function cancel_running_tasks() {
    access::verify_csrf();
    Database::instance()->update(
      "tasks",
      array("done" => 1, "state" => "cancelled"),
      array("done" => 0));
    message::success(t("All running tasks cancelled"));
    url::redirect("admin/maintenance");
  }

  /**
   * Remove a task.
   * @param string $task_id
   */
  public function remove($task_id) {
    access::verify_csrf();

    task::remove($task_id);

    message::success(t("Task removed"));
    url::redirect("admin/maintenance");
  }

  public function remove_finished_tasks() {
    access::verify_csrf();
    Database::instance()->delete("tasks", array("done" => 1));
    message::success(t("All finished tasks removed"));
    url::redirect("admin/maintenance");
  }

  /**
   * Run a task.  This will trigger the task to do a small amount of work, then it will report
   * back with status on the task.
   * @param string $task_id
   */
  public function run($task_id) {
    access::verify_csrf();

    $task = task::run($task_id);

    if ($task->done) {
      switch ($task->state) {
      case "success":
        log::success("tasks", t("Task %task_name completed (task id %task_id)",
                                array("task_name" => $task->name, "task_id" => $task->id)),
                     html::anchor(url::site("admin/maintenance"), t("maintenance")));
        message::success(t("Task completed successfully"));
        break;

      case "error":
        log::error("tasks", t("Task %task_name failed (task id %task_id)",
                              array("task_name" => $task->name, "task_id" => $task->id)),
                   html::anchor(url::site("admin/maintenance"), t("maintenance")));
        message::success(t("Task failed"));
        break;
      }
      print json_encode(array("result" => "success",
                              "task" => array(
                                "percent_complete" => $task->percent_complete,
                                "status" => $task->status,
                                "done" => $task->done),
                              "location" => url::site("admin/maintenance")));

    } else {
      print json_encode(array("result" => "in_progress",
                              "task" => array(
                                "percent_complete" => $task->percent_complete,
                                "status" => $task->status,
                                "done" => $task->done)));
    }
  }
}
