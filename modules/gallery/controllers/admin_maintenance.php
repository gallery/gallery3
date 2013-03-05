<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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
    $query = db::build()
      ->update("tasks")
      ->set("state", "stalled")
      ->where("done", "=", 0)
      ->where("state", "<>", "stalled")
      ->where(db::expr("UNIX_TIMESTAMP(NOW()) - `updated` > 15"))
      ->execute();
    $stalled_count = $query->count();
    if ($stalled_count) {
      log::warning("tasks",
                   t2("One task is stalled",
                      "%count tasks are stalled",
                      $stalled_count),
                   t('<a href="%url">view</a>',
                     array("url" => html::mark_clean(url::site("admin/maintenance")))));
    }

    $view = new Admin_View("admin.html");
    $view->page_title = t("Maintenance tasks");
    $view->content = new View("admin_maintenance.html");
    $view->content->task_definitions = task::get_definitions();
    $view->content->running_tasks = ORM::factory("task")
      ->where("done", "=", 0)->order_by("updated", "DESC")->find_all();
    $view->content->finished_tasks = ORM::factory("task")
      ->where("done", "=", 1)->order_by("updated", "DESC")->find_all();
    print $view;

    // Do some maintenance while we're in here
    db::build()
      ->delete("caches")
      ->where("expiration", "<>", 0)
      ->where("expiration", "<=", time())
      ->execute();
    module::deactivate_missing_modules();
  }

  /**
   * Start a new task
   * @param string $task_callback
   */
  public function start($task_callback) {
    access::verify_csrf();

    $task = task::start($task_callback);
    $view = new View("admin_maintenance_task.html");
    $view->task = $task;

    log::info("tasks", t("Task %task_name started (task id %task_id)",
                         array("task_name" => $task->name, "task_id" => $task->id)),
              html::anchor("admin/maintenance", t("maintenance")));
    print $view;
  }

  /**
   * Resume a stalled task
   * @param string $task_id
   */
  public function resume($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);
    if (!$task->loaded()) {
      throw new Exception("@todo MISSING_TASK");
    }
    $view = new View("admin_maintenance_task.html");
    $view->task = $task;

    $task->log(t("Task %task_name resumed (task id %task_id)",
                 array("task_name" => $task->name, "task_id" => $task->id)));
    log::info("tasks", t("Task %task_name resumed (task id %task_id)",
                         array("task_name" => $task->name, "task_id" => $task->id)),
              html::anchor("admin/maintenance", t("maintenance")));
    print $view;
  }

  /**
   * Show the task log
   * @param string $task_id
   */
  public function show_log($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);
    if (!$task->loaded()) {
      throw new Exception("@todo MISSING_TASK");
    }
    $view = new View("admin_maintenance_show_log.html");
    $view->task = $task;

    print $view;
  }

  /**
   * Save the task log
   * @param string $task_id
   */
  public function save_log($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);
    if (!$task->loaded()) {
      throw new Exception("@todo MISSING_TASK");
    }

    header("Content-Type: application/text");
    header("Content-Disposition: filename=gallery3_task_log.txt");
    print $task->get_log();
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
    db::build()
      ->update("tasks")
      ->set("done", 1)
      ->set("state", "cancelled")
      ->where("done", "=", 0)
      ->execute();
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

    // Do it the long way so we can call delete and remove the cache.
    $finished = ORM::factory("task")
      ->where("done", "=", 1)
      ->find_all();
    foreach ($finished as $task) {
      task::remove($task->id);
    }
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

    try {
      $task = task::run($task_id);
    } catch (Exception $e) {
      Kohana_Log::add(
        "error",
        sprintf(
          "%s in %s at line %s:\n%s", $e->getMessage(), $e->getFile(),
          $e->getLine(), $e->getTraceAsString()));
      throw $e;
    }

    if ($task->done) {
      switch ($task->state) {
      case "success":
        log::success("tasks", t("Task %task_name completed (task id %task_id)",
                                array("task_name" => $task->name, "task_id" => $task->id)),
                     html::anchor("admin/maintenance", t("maintenance")));
        message::success(t("Task completed successfully"));
        break;

      case "error":
        log::error("tasks", t("Task %task_name failed (task id %task_id)",
                              array("task_name" => $task->name, "task_id" => $task->id)),
                   html::anchor("admin/maintenance", t("maintenance")));
        message::success(t("Task failed"));
        break;
      }
      // Using sprintf("%F") to avoid comma as decimal separator.
      json::reply(array("result" => "success",
                        "task" => array(
                          "percent_complete" => sprintf("%F", $task->percent_complete),
                          "status" => (string) $task->status,
                          "done" => (bool) $task->done),
                        "location" => url::site("admin/maintenance")));

    } else {
      json::reply(array("result" => "in_progress",
                        "task" => array(
                          "percent_complete" => sprintf("%F", $task->percent_complete),
                          "status" => (string) $task->status,
                          "done" => (bool) $task->done)));
    }
  }

  public function maintenance_mode($value) {
    access::verify_csrf();
    module::set_var("gallery", "maintenance_mode", $value);
    url::redirect("admin/maintenance");
  }
}
