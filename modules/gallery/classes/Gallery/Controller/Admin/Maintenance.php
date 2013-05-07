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
class Gallery_Controller_Admin_Maintenance extends Controller_Admin {
  /**
   * Show a list of all available, running and finished tasks.
   */
  public function action_index() {
    $stalled_count = DB::update("tasks")
      ->set(array("state" => "stalled"))
      ->where("done", "=", 0)
      ->where("state", "<>", "stalled")
      ->where("updated", "<", DB::expr("UNIX_TIMESTAMP() - 15"))
      ->execute();
    if ($stalled_count) {
      GalleryLog::warning("tasks",
                   t2("One task is stalled",
                      "%count tasks are stalled",
                      $stalled_count),
                   t('<a href="%url">view</a>',
                     array("url" => HTML::mark_clean(URL::site("admin/maintenance")))));
    }

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Maintenance tasks");
    $view->content = new View("admin/maintenance.html");
    $view->content->task_definitions = Task::get_definitions();
    $view->content->running_tasks = ORM::factory("Task")
      ->where("done", "=", 0)->order_by("updated", "DESC")->find_all();
    $view->content->finished_tasks = ORM::factory("Task")
      ->where("done", "=", 1)->order_by("updated", "DESC")->find_all();
    $this->response->body($view);

    // Do some maintenance while we're in here
    DB::delete("caches")
      ->where("expiration", "<>", 0)
      ->where("expiration", "<=", time())
      ->execute();
    Module::deactivate_missing_modules();
  }

  /**
   * Start a new task
   * @param string $task_callback
   */
  public function action_start() {
    $task_callback = $this->request->arg(0);
    Access::verify_csrf();

    $task = Task::start($task_callback);
    $view = new View("admin/maintenance_task.html");
    $view->task = $task;

    GalleryLog::info("tasks", t("Task %task_name started (task id %task_id)",
                         array("task_name" => $task->name, "task_id" => $task->id)),
              HTML::anchor("admin/maintenance", t("maintenance")));
    $this->response->body($view);
  }

  /**
   * Resume a stalled task
   * @param string $task_id
   */
  public function action_resume() {
    $task_id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    $task = ORM::factory("Task", $task_id);
    if (!$task->loaded()) {
      throw new Gallery_Exception("Missing task");
    }
    $view = new View("admin/maintenance_task.html");
    $view->task = $task;

    $task->log(t("Task %task_name resumed (task id %task_id)",
                 array("task_name" => $task->name, "task_id" => $task->id)));
    GalleryLog::info("tasks", t("Task %task_name resumed (task id %task_id)",
                         array("task_name" => $task->name, "task_id" => $task->id)),
              HTML::anchor("admin/maintenance", t("maintenance")));
    $this->response->body($view);
  }

  /**
   * Show the task log
   * @param string $task_id
   */
  public function action_show_log() {
    $task_id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    $task = ORM::factory("Task", $task_id);
    if (!$task->loaded()) {
      throw new Gallery_Exception("Missing task");
    }
    $view = new View("admin/maintenance_show_log.html");
    $view->task = $task;

    $this->response->body($view);
  }

  /**
   * Save the task log
   * @param string $task_id
   */
  public function action_save_log() {
    $task_id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    $task = ORM::factory("Task", $task_id);
    if (!$task->loaded()) {
      throw new Gallery_Exception("Missing task");
    }

    // Send the log as an attachment for download.
    $this->response->body($task->get_log());
    $this->response->send_file(true, "gallery3_task_log.txt",
                               array("mime_type" => "application/text"));
  }

  /**
   * Cancel a task.
   * @param string $task_id
   */
  public function action_cancel() {
    $task_id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    Task::cancel($task_id);

    Message::success(t("Task cancelled"));
    $this->redirect("admin/maintenance");
  }

  public function action_cancel_running_tasks() {
    Access::verify_csrf();
    DB::update("tasks")
      ->set(array("done" => 1, "state" => "cancelled"))
      ->where("done", "=", 0)
      ->execute();
    Message::success(t("All running tasks cancelled"));
    $this->redirect("admin/maintenance");
  }

  /**
   * Remove a task.
   * @param string $task_id
   */
  public function action_remove() {
    $task_id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    Task::remove($task_id);

    Message::success(t("Task removed"));
    $this->redirect("admin/maintenance");
  }

  public function action_remove_finished_tasks() {
    Access::verify_csrf();

    // Do it the long way so we can call delete and remove the cache.
    $finished = ORM::factory("Task")
      ->where("done", "=", 1)
      ->find_all();
    foreach ($finished as $task) {
      Task::remove($task->id);
    }
    Message::success(t("All finished tasks removed"));
    $this->redirect("admin/maintenance");
  }

  /**
   * Run a task.  This will trigger the task to do a small amount of work, then it will report
   * back with status on the task.
   * @param string $task_id
   */
  public function action_run() {
    $task_id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    try {
      $task = Task::run($task_id);
    } catch (Exception $e) {
      Log::instance()->add(
        Log::ERROR,
        sprintf(
          "%s in %s at line %s:\n%s", $e->getMessage(), $e->getFile(),
          $e->getLine(), $e->getTraceAsString()));
      throw $e;
    }

    if ($task->done) {
      switch ($task->state) {
      case "success":
        GalleryLog::success("tasks", t("Task %task_name completed (task id %task_id)",
                                array("task_name" => $task->name, "task_id" => $task->id)),
                     HTML::anchor("admin/maintenance", t("maintenance")));
        Message::success(t("Task completed successfully"));
        break;

      case "error":
        GalleryLog::error("tasks", t("Task %task_name failed (task id %task_id)",
                              array("task_name" => $task->name, "task_id" => $task->id)),
                   HTML::anchor("admin/maintenance", t("maintenance")));
        Message::success(t("Task failed"));
        break;
      }
      // Using sprintf("%F") to avoid comma as decimal separator.
      $this->response->json(array("result" => "success",
                                  "task" => array(
                                    "percent_complete" => sprintf("%F", $task->percent_complete),
                                    "status" => (string) $task->status,
                                    "done" => (bool) $task->done),
                                  "location" => URL::site("admin/maintenance")));

    } else {
      $this->response->json(array("result" => "in_progress",
                                  "task" => array(
                                    "percent_complete" => sprintf("%F", $task->percent_complete),
                                    "status" => (string) $task->status,
                                    "done" => (bool) $task->done)));
    }
  }

  public function action_maintenance_mode() {
    $value = $this->request->arg(0, "digit");
    Access::verify_csrf();
    Module::set_var("gallery", "maintenance_mode", $value);
    $this->redirect("admin/maintenance");
  }
}
