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
class Admin_Developer_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_developer.html");
    if (!is_writable(MODPATH)) {
      message::warning(
        t("The module directory is not writable. Please insure that it is writable by the web server"));
    }
    list ($form, $errors) = $this->_get_module_form();
    $view->content->module_create = $this->_get_module_create_content($form, $errors);
    print $view;
  }

  public function module_create() {
    access::verify_csrf();
      
    list ($form, $errors) = $this->_get_module_form();

    $post = new Validation($_POST);
    $post->add_rules("name", "required");
    $post->add_rules("description", "required");
    $post->add_callbacks("name", array($this, "_is_module_defined"));
    
    if ($post->validate()) {
      $task_def = Task_Definition::factory()
        ->callback("developer_task::create_module")
        ->description(t("Create a new module"))
        ->name(t("Create Module"));
      $task = task::create($task_def, array_merge(array("step" => 0), $post->as_array()));

      print json_encode(array("result" => "started",
                            "url" => url::site("admin/developer/run_create/{$task->id}?csrf=" .
                                               access::csrf_token()),
                            "task" => $task->as_array()));
    } else {
      $v = $this->_get_module_create_content(arr::overwrite($form, $post->as_array()),
        arr::overwrite($errors, $post->errors()));
      print json_encode(array("result" => "error",
                              "form" => $v->__toString()));
    }
  }

  public function run_create($task_id) {
    access::verify_csrf();

    $task = task::run($task_id);

    if ($task->done) {
      $context = unserialize($task->context);
      switch ($task->state) {
      case "success":
        message::success(t("Generation of %module completed successfully",
                           array("module" => $context["name"])));
        break;

      case "error":
        message::success(t("Generation of %module failed.",
                           array("module" => $context["name"])));
        break;
      }
      print json_encode(array("result" => "success",
                              "task" => $task->as_array()));

    } else {
      print json_encode(array("result" => "in_progress",
                              "task" => $task->as_array()));
    }
  }

  private function _get_module_create_content($form, $errors) {
    $config = Kohana::config("developer.methods");

    $v = new View("developer_module.html");
    $v->action = "admin/developer/module_create";
    $v->hidden = array("csrf" => access::csrf_token());
    $v->theme = $config["theme"];
    $v->event = $config["event"];
    $v->menu = $config["menu"];
    $v->form = $form;
    $v->errors = $errors;
    return $v;
  }

  public function _is_module_defined(Validation $post, $field) {
    $module_name = $post[$field];
    if (file_exists(MODPATH . "$module_name/module.info")) {
      $post->add_error($field, "module_exists");
    }
  }
  
  private function _get_module_form($name="", $description="") {
    $form = array("name" => "", "description" => "", "theme[]" => array(), "menu[]" => array(),
                  "event[]" => array());
    $errors = array_fill_keys(array_keys($form), "");
    
    return array($form, $errors);
  }
}
