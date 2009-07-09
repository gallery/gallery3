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
class Server_Add_Controller extends Admin_Controller {
  public function browse($id) {
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

  public function children() {
    $path = $this->input->get("path");
    if (!server_add::is_valid_path($path)) {
      throw new Exception("@todo BAD_PATH");
    }

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

  public function start() {
    access::verify_csrf();

    $item = ORM::factory("item", Input::instance()->get("item_id"));
    // We're an admin so this isn't necessary, but we'll eventually open this up to non-admins and
    // this also verifies that the item was loaded properly.
    access::required("edit", $item);

    // Gather up all the paths and associate them by directory, so that we can locate any empty
    // directories for the next round.
    foreach (Input::instance()->post("paths") as $path) {
      if (is_dir($path)) {
        $selections[$path] = array();
      } else if (is_file($path)) {
        $selections[dirname($path)][] = $path;
      }
    }

    $task_def = Task_Definition::factory()
      ->callback("Server_Add_Controller::add")
      ->description(t("Add photos or movies from the local server"))
      ->name(t("Add from server"));
    $task = task::create(
      $task_def, array("item_id" => $item->id, "selections" => $selections));

    print json_encode(
      array("result" => "started",
            "url" => url::site("server_add/run/$task->id?csrf=" . access::csrf_token())));
  }

  function run($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);
    if (!$task->loaded || $task->owner_id != user::active()->id) {
      access::forbidden();
    }

    $task = task::run($task_id);
    print json_encode(array("done" => $task->done,
                            "percent_complete" => $task->percent_complete));
  }

  /**
   * This is the task code that adds photos and albums.  It first examines all the target files
   * and creates a set of Server_Add_File_Models, then runs through the list of models and adds
   * them one at a time.
   */
  static function add($task) {
    $selections = $task->get("selections");
    $mode = $task->get("mode", "init");
    $start = microtime(true);
    $item_id = $task->get("item_id");

    switch ($mode) {
    case "init":
      $task->set("mode", "build-file-list");
      $task->set("queue", array_keys($selections));
      $task->percent_complete = 0;
      batch::start();
      break;

    case "build-file-list":  /* 0% to 10% */
      // We can't fit an arbitrary number of paths in a task, so store them in a separate table.
      // Don't use an iterator here because we can't get enough control over it when we're dealing
      // with a deep hierarchy and we don't want to go over our time quota.
      $queue = $task->get("queue");
      Kohana::log("alert",print_r($queue,1));
      while ($queue && microtime(true) - $start < 0.5) {
        $file = array_shift($queue);
        $entry = ORM::factory("server_add_file");
        $entry->task_id = $task->id;
        $entry->file = $file;
        $entry->save();

        if (is_dir($file)) {
          $queue = array_merge(
            $queue, empty($selections[$file]) ? glob("$file/*") : $selections[$file]);
        }
      }
      // We have no idea how long this can take because we have no idea how deep the tree
      // hierarchy rabbit hole goes.  Leave ourselves room here for 100 iterations and don't go
      // over 10% in percent_complete.
      $task->set("queue", $queue);
      $task->percent_complete = min($task->percent_complete + 0.1, 10);

      if (!$queue) {
        $task->set("mode", "add-files");
        $task->set(
          "total_files", database::instance()->count_records(
            "server_add_files", array("task_id" => $task->id)));
        $task->set("albums", array());
        $task->set("completed", 0);
        $task->percent_complete = 10;
      }
      break;

    case "add-files": /* 10% to 100% */
      $completed_files = $task->get("completed_files");
      $total_files = $task->get("total_files");
      $albums = $task->get("albums");

      // Ordering by id ensures that we add them in the order that we created the entries, which
      // will create albums first.
      $entries = ORM::factory("server_add_file")
        ->where("task_id", $task->id)
        ->orderby("id", "ASC")
        ->limit(10)
        ->find_all();
      if ($entries->count() == 0) {
        $task->set("mode", "done");
      }

      $item = model_cache::get("item", $item_id);
      foreach ($entries as $entry) {
        if (microtime(true) - $start > 0.5) {
          break;
        }

        $relative_path = self::_relative_path($entry->file);
        $name = basename($relative_path);
        $title = item::convert_filename_to_title($name);
        if (is_dir($entry->file)) {
          if (isset($albums[$relative_path]) && $parent_id = $albums[$relative_path]) {
            $parent = ORM::factory("item", $parent_id);
          } else {
            $album = album::create($item, $name, $title, null, user::active()->id);
            $albums[$relative_path] = $album->id;
            $task->set("albums", $albums);
          }
        } else {
          if (strpos($relative_path, "/") !== false) {
            $parent = ORM::factory("item", $albums[dirname($relative_path)]);
          } else {
            $parent = $item;
          }

          $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
          if (in_array($extension, array("gif", "png", "jpg", "jpeg"))) {
            photo::create($parent, $entry->file, $name, $title, null, user::active()->id);
          } else if (in_array($extension, array("flv", "mp4"))) {
            movie::create($parent, $entry->file, $name, $title, null, user::active()->id);
          } else {
            // Unsupported type
            // @todo: $task->log this
          }
        }

        $completed_files++;
        $entry->delete();
      }
      $task->set("completed_files", $completed_files);
      $task->percent_complete = 10 + 100 * ($completed_files / $total_files);
      Kohana::log("alert",print_r($task->as_array(),1));
      break;

    case "done":
      batch::stop();
      $task->done = true;
      $task->state = "success";
      $task->percent_complete = 100;
      message::info(t2("Successfully added one photo",
                       "Successfully added %count photos",
                       $task->get("completed_files")));
    }
  }

  /**
   * Given a path that's somewhere in our authorized_paths list, return just the part that's
   * relative to the nearest authorized path.
   */
  static function _relative_path($path) {
    static $authorized_paths;
    // @todo this doesn't deal well with overlapping authorized paths, it'll just use the first one
    // that matches.  If we sort $authorized_paths by length in descending order, that should take
    // care of the problem.
    if (!$authorized_paths) {
      $authorized_paths =
        array_keys(unserialize(module::get_var("server_add", "authorized_paths")));
    }

    foreach ($authorized_paths as $candidate) {
      $candidate = dirname($candidate);
      if (strpos($path, $candidate) === 0) {
        return substr($path, strlen($candidate) + 1);
      }
    }

    throw new Exception("@todo BAD_PATH");
  }
}
