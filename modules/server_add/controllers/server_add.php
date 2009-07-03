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
class Server_Add_Controller extends Controller {
  public function browse($id) {
    if (!user::active()->admin) {
      access::forbidden();
    }


    $paths = unserialize(module::get_var("server_add", "authorized_paths"));
    foreach (array_keys($paths) as $path) {
      $files[$path] = basename($path);
    }

    $item = ORM::factory("item", $id);
    $view = new View("server_add_tree_dialog.html");
    $view->item = $item;
    $view->tree = new View("server_add_tree.html");
    $view->tree->files = $files;
    print $view;
  }

  private function _validate_path($path) {
    if (!is_readable($path) || is_link($path)) {
      throw new Exception("@todo BAD_PATH");
    }

    $authorized_paths = unserialize(module::get_var("server_add", "authorized_paths"));
    foreach (array_keys($authorized_paths) as $valid_path) {
      if (strpos($path, $valid_path) === 0) {
        return;
      }
    }

    throw new Exception("@todo BAD_PATH");
  }

  public function children() {
    if (!user::active()->admin) {
      access::forbidden();
    }

    $path = $this->input->get("path");
    $this->_validate_path($path);

    $tree = new View("server_add_tree.html");
    $tree->files = array();
    $tree->tree_id = substr(md5($path), 10);

    foreach (glob("$path/*") as $file) {
      if (!is_readable($file)) {
        continue;
      }

      if (!is_dir($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, array("gif", "jpeg", "jpg", "png", "flv", "mp4"))) {
          continue;
        }
      }

      $tree->files[$file] = basename($file);
    }
    print $tree;
  }

  public function add() {
    if (!user::active()->admin) {
      access::forbidden();
    }
    access::verify_csrf();

    $authorized_paths = unserialize(module::get_var("server_add", "authorized_paths"));

    // The paths we receive are full pathnames.  Convert that into a tree structure to save space
    // in our task.
    foreach (Input::instance()->post("path") as $path) {
      if (is_dir($path)) {
        $dirs[$path] = array();
      } else if (is_file($path)) {
        $dir = dirname($path);
        $file = basename($path);
        $dirs[$dir][] = $file;
      }
    }

    Kohana::log("alert",print_r($dirs,1));
  }

  /* ================================================================================ */

  function start($id) {
    if (!user::active()->admin) {
      access::forbidden();
    }
    access::verify_csrf();

    $item = ORM::factory("item", $id);
    $paths = unserialize(module::get_var("server_add", "authorized_paths"));
    $input_files = $this->input->post("path");
    $collapsed = $this->input->post("collapsed");
    $files = array();
    $total_count = 0;
    foreach (array_keys($paths) as $valid_path) {
      $path_length = strlen($valid_path);
      foreach ($input_files as $key => $path) {
        if (!empty($path)) {
          if ($valid_path != $path && strpos($path, $valid_path) === 0) {
            $relative_path = substr(dirname($path), $path_length);
            $name = basename($path);
            $files[$valid_path][] = array("path" => $relative_path,
                                          "parent_id" => $id, "name" => basename($path),
                                        "type" => is_dir($path) ? "album" : "file");
            $total_count++;
          }
          if ($collapsed[$key] === "true") {
            $total_count += $this->_select_children($id, $valid_path, $path, $files[$valid_path]);
          }
          unset($input_files[$key]);
          unset($collapsed[$key]);
        }
      }
    }

    if ($total_count == 0) {
      print json_encode(array("result" => "success",
                              "url" => "",
                              "task" => array(
                                "id" => -1, "done" => 1, "percent_complete" => 100,
                                "status" => t("No Eligible files, import cancelled"))));
      return;
    }

    $task_def = Task_Definition::factory()
      ->callback("server_add_task::add_from_server")
      ->description(t("Add photos or movies from the local server"))
      ->name(t("Add from server"));
    $task = task::create($task_def, array("item_id" => $id, "next_path" => 0, "files" => $files,
      "counter" => 0, "position" => 0, "total" => $total_count));

    batch::start();
    print json_encode(array("result" => "started",
                            "url" => url::site("server_add/add_photo/{$task->id}?csrf=" .
                                               access::csrf_token()),
                            "task" => array(
                              "id" => $task->id,
                              "percent_complete" => $task->percent_complete,
                              "status" => $task->status,
                              "done" => $task->done)));
  }

  function add_photo($task_id) {
    if (!user::active()->admin) {
      access::forbidden();
    }
    access::verify_csrf();

    $task = task::run($task_id);
    // @todo the task is already run... its a little late to check the access
    if (!$task->loaded || $task->owner_id != user::active()->id) {
      access::forbidden();
    }

    if ($task->done) {
      switch ($task->state) {
      case "success":
        message::success(t("Add from server completed"));
        break;

      case "error":
        message::warning(t("Add from server completed with errors"));
        break;
      }
      print json_encode(array("result" => "success",
                              "task" => array(
                                "id" => $task->id,
                                "percent_complete" => $task->percent_complete,
                                "status" => $task->status,
                                "done" => $task->done)));

    } else {
      print json_encode(array("result" => "in_progress",
                              "task" => array(
                                "id" => $task->id,
                                "percent_complete" => $task->percent_complete,
                                "status" => $task->status,
                                "done" => $task->done)));
    }
  }

  public function finish($id, $task_id) {
    if (!user::active()->admin) {
      access::forbidden();
    }
    access::verify_csrf();
    $task = ORM::factory("task", $task_id);

    if (!$task->loaded || $task->owner_id != user::active()->id) {
      access::forbidden();
    }

    if (!$task->done) {
      message::warning(t("Add from server was cancelled prior to completion"));
    }

    batch::stop();
    print json_encode(array("result" => "success"));
  }

  public function pause($id, $task_id) {
    if (!user::active()->admin) {
      access::forbidden();
    }
    access::verify_csrf();
    $task = ORM::factory("task", $task_id);
    if (!$task->loaded || $task->owner_id != user::active()->id) {
      access::forbidden();
    }

    message::warning(t("Add from server was cancelled prior to completion"));
    batch::stop();
    print json_encode(array("result" => "success"));
  }

  private function _select_children($id, $valid_path, $path, &$files) {
    $count = 0;
    $children = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($path),
      RecursiveIteratorIterator::SELF_FIRST);

    $path_length = strlen($valid_path);
    foreach($children as $name => $file){
      if ($file->isLink()) {
        continue;
      }
      $filename = $file->getFilename();
      if ($filename[0] != ".") {
        if ($file->isDir()) {
          $relative_path = substr(dirname($file->getPathname()), $path_length);
          $files[] = array("path" => $relative_path,
                           "parent_id" => $id, "name" => $filename, "type" => "album");
          $count++;
        } else {
          $extension = strtolower(substr(strrchr($filename, '.'), 1));
          if ($file->isReadable() &&
              in_array($extension, array("gif", "jpeg", "jpg", "png", "flv", "mp4"))) {
            $relative_path = substr(dirname($file->getPathname()), $path_length);
            $files[] = array("path" => $relative_path,
                             "parent_id" => $id, "name" => $filename, "type" => "file");
            $count++;
          }
        }
      }

    }

    return $count;
  }
}