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
class Server_Add_Controller extends Controller {
  public function index($id) {
    $paths = unserialize(module::get_var("server_add", "authorized_paths"));

    $item = ORM::factory("item", $id);
    access::required("server_add", $item);

    $view = new View("server_add_tree_dialog.html");
    $view->action = url::site("__ARGS__/{$id}__TASK_ID__?csrf=" . access::csrf_token());
    $view->parents = $item->parents();
    $view->album_title = $item->title;

    $tree = new View("server_add_tree.html");
    $tree->data = array();
    $tree->tree_id = "tree_$id";
    foreach (array_keys($paths) as $path) {
      $tree->data[$path] = array("path" => $path, "is_dir" => true);
    }
    $view->tree = $tree->__toString();
    print $view;
  }

  public function children() {
    $paths = unserialize(module::get_var("server_add", "authorized_paths"));

    $path_valid = false;
    $path = $this->input->post("path");

    foreach (array_keys($paths) as $valid_path) {
      if ($path_valid = strpos($path, $valid_path) === 0) {
        break;
      }
    }
    if (empty($path_valid)) {
      throw new Exception("@todo BAD_PATH");
    }

    if (!is_readable($path) || is_link($path)) {
      kohana::show_404();
    }

    $tree = new View("server_add_tree.html");
    $tree->data = $this->_get_children($path);
    $tree->tree_id = "tree_" . md5($path);
    print $tree;
  }

  function start($id) {
    access::verify_csrf();
    $paths = unserialize(module::get_var("server_add", "authorized_paths"));
    $input_files = $this->input->post("path");
    $files = array();
    $total_count = 0;
    foreach (array_keys($paths) as $valid_path) {
      $path_length = strlen($valid_path);
      foreach ($input_files as $key => $path) {
        if ($valid_path != $path && strpos($path, $valid_path) === 0) {
          $relative_path = substr(dirname($path), $path_length);
          $name = basename($path);
          $files[$valid_path][] = array("path" => $relative_path,
                                        "parent_id" => $id, "name" => basename($path),
                                        "type" => is_dir($path) ? "album" : "file");
          $total_count++;
          unset($input_files[$key]);
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
    access::verify_csrf();

    $task = task::run($task_id);

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
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);

    if (!$task->done) {
      message::warning(t("Add from server was cancelled prior to completion"));
    }

    batch::stop();
    print json_encode(array("result" => "success"));
  }

  public function pause($id, $task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);

    message::warning(t("Add from server was cancelled prior to completion"));
    batch::stop();
    print json_encode(array("result" => "success"));
  }

  private function _get_children($path) {
    $directory_list = $file_list = array();
    $files = new DirectoryIterator($path);
    foreach ($files as $file) {
      if ($file->isDot() || $file->isLink()) {
        continue;
      }
      $filename = $file->getFilename();
      if ($filename[0] != ".") {
        if ($file->isDir()) {
          $directory_list[$filename] = array("path" => $file->getPathname(), "is_dir" => true);
        } else {
          $extension = strtolower(substr(strrchr($filename, '.'), 1));
          if ($file->isReadable() &&
              in_array($extension, array("gif", "jpeg", "jpg", "png", "flv", "mp4"))) {
            $file_list[$filename] = array("path" => $file->getPathname(), "is_dir" => false);
          }
        }
      }
    }

    ksort($directory_list);
    ksort($file_list);

    // We can't use array_merge here because if a file name is numeric, it will
    // get renumbered, so lets do it ourselves
    foreach ($file_list as $file => $fileinfo) {
      $directory_list[$file] = $fileinfo;
    }
    return $directory_list;
  }
}