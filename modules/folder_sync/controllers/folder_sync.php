<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
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
class Folder_Sync_Controller extends Admin_Controller {
  public function browse($id) {
    $paths = unserialize(module::get_var("folder_sync", "authorized_paths"));
    foreach (array_keys($paths) as $path) {
      $files[] = $path;
    }

    $item = ORM::factory("item", $id);
    $view = new View("folder_sync_tree_dialog.html");
    $view->item = $item;
    $view->tree = new View("folder_sync_tree.html");
    $view->tree->files = $files;
    $view->tree->parents = array();
    print $view;
  }

  public function children() {
    $path = Input::instance()->get("path");

    $tree = new View("folder_sync_tree.html");
    $tree->files = array();
    $tree->parents = array();

    // Make a tree with the parents back up to the authorized path, and all the children under the
    // current path.
    if (folder_sync::is_valid_path($path)) {
      $tree->parents[] = $path;
      while (folder_sync::is_valid_path(dirname($tree->parents[0]))) {
        array_unshift($tree->parents, dirname($tree->parents[0]));
      }

      $glob_path = str_replace(array("{", "}", "[", "]"), array("\{", "\}", "\[", "\]"), $path);
      foreach (glob("$glob_path/*") as $file) {
        if (!is_readable($file)) {
          continue;
        }
        if (!is_dir($file)) {
          $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          if (!in_array($ext, array("gif", "jpeg", "jpg", "png", "flv", "mp4", "m4v"))) {
            continue;
          }
        }

        $tree->files[] = $file;
      }
    } else {
      // Missing or invalid path; print out the list of authorized path
      $paths = unserialize(module::get_var("folder_sync", "authorized_paths"));
      foreach (array_keys($paths) as $path) {
        $tree->files[] = $path;
      }
    }
    print $tree;
  }

  /**
   * Begin the task of adding photos.
   */
  public function start() {
    access::verify_csrf();
    $item = ORM::factory("item", Input::instance()->get("item_id"));

    $task_def = Task_Definition::factory()
      ->callback("Folder_Sync_Controller::add")
      ->description(t("Add photos or movies from the local server"))
      ->name(t("Add from server"));
    $task = task::create($task_def, array("item_id" => $item->id));

    foreach (Input::instance()->post("paths") as $path) {
      if (folder_sync::is_valid_path($path)) {
        $entry = ORM::factory("folder_sync_entry");
        $entry->path = $path;
        $entry->is_directory = intval(is_dir($path));
        $entry->parent_id = null;
        $entry->task_id = $task->id;
        $entry->md5 = '';
        $entry->save();
      }
    }

    json::reply(
      array("result" => "started",
            "status" => (string)$task->status,
            "url" => url::site("folder_sync/run/$task->id?csrf=" . access::csrf_token())));
  }

  /**
   * Run the task of adding photos
   */
  function run($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);
    if (!$task->loaded() || $task->owner_id != identity::active_user()->id) {
      access::forbidden();
    }

    $task = task::run($task_id);
    // Prevent the JavaScript code from breaking by forcing a period as
    // decimal separator for all locales with sprintf("%F", $value).
    json::reply(array("done" => (bool)$task->done,
                      "status" => (string)$task->status,
                      "percent_complete" => sprintf("%F", $task->percent_complete)));
  }

  /**
   * This is the task code that adds photos and albums.  It first examines all the target files
   * and creates a set of Folder_Sync_Entry_Models, then runs through the list of models and adds
   * them one at a time.
   */
  static function add($task) {
    $mode = $task->get("mode", "init");
    $start = microtime(true);

    switch ($mode) {
    case "init":
      $task->set("mode", "build-file-list");
      $task->set("dirs_scanned", 0);
      $task->percent_complete = 0;
      $task->status = t("Starting up");
      batch::start();
      break;

    case "build-file-list":  // 0% to 10%
      // We can't fit an arbitrary number of paths in a task, so store them in a separate table.
      // Don't use an iterator here because we can't get enough control over it when we're dealing
      // with a deep hierarchy and we don't want to go over our time quota.
      $paths = unserialize(module::get_var("folder_sync", "authorized_paths"));
      $dirs_scanned = $task->get("dirs_scanned");
      while (microtime(true) - $start < 0.5) {
        // Process every directory that doesn't yet have a parent id, these are the
        // paths that we're importing.
        $entry = ORM::factory("folder_sync_entry")
          ->where("task_id", "=", $task->id)
          ->where("is_directory", "=", 1)
          ->where("checked", "=", 0)
          ->order_by("id", "ASC")
          ->find();

        if ($entry->loaded()) {
          $child_paths = glob(preg_quote($entry->path) . "/*");
          if (!$child_paths) {
            $child_paths = glob("{$entry->path}/*");
          }
          foreach ($child_paths as $child_path) {
            if (!is_dir($child_path)) {
              $ext = strtolower(pathinfo($child_path, PATHINFO_EXTENSION));
              if (!in_array($ext, array("gif", "jpeg", "jpg", "png", "flv", "mp4", "m4v")) ||
                  !filesize($child_path)) {
                // Not importable, skip it.
                continue;
              }
              // check if file was already imported
              if(module::get_var("folder_sync", "skip_duplicates")) {
                $entry_exists = ORM::factory("folder_sync_entry")
                  ->where("is_directory", "=", 0)
                  ->where("path", "=", $child_path)
                  ->find()->loaded();
                if(entry_exists) {
                  if(!module::get_var("folder_sync", "process_updates")) {
                    continue;
                  }
                }
              }
            }
            
            $child_entry = ORM::factory("folder_sync_entry");
            $child_entry->task_id = $task->id;
            $child_entry->path = $child_path;
            $child_entry->parent_id = $entry->id;  // null if the parent was a staging dir
            $child_entry->is_directory = is_dir($child_path);
            $child_entry->md5 = is_dir($child_path) ? '' : md5_file($child_path);
            $child_entry->save();
          }

          // We've processed this entry, mark it as done.
          $entry->checked = 1;
          $entry->save();
          $dirs_scanned++;
        }
      }

      // We have no idea how long this can take because we have no idea how deep the tree
      // hierarchy rabbit hole goes.  Leave ourselves room here for 100 iterations and don't go
      // over 10% in percent_complete.
      $task->set("dirs_scanned", $dirs_scanned);
      $task->percent_complete = min($task->percent_complete + 0.1, 10);
      $task->status = t2("Scanned one directory", "Scanned %count directories", $dirs_scanned);

      if (!$entry->loaded()) {
        $task->set("mode", "add-files");
        $task->set(
          "total_files",
          ORM::factory("folder_sync_entry")->where("task_id", "=", $task->id)->count_all());
        $task->percent_complete = 10;
      }
      break;

    case "add-files": // 10% to 100%
      $completed_files = $task->get("completed_files", 0);
      $total_files = $task->get("total_files");

      // Ordering by id ensures that we add them in the order that we created the entries, which
      // will create albums first.  Ignore entries which already have an Item_Model attached,
      // they're done.
      $entries = ORM::factory("folder_sync_entry")
        ->where("task_id", "=", $task->id)
        ->where("item_id", "IS", null)
        ->order_by("id", "ASC")
        ->limit(10)
        ->find_all();
      if ($entries->count() == 0) {
        // Out of entries, we're done.
        $task->set("mode", "done");
      }

      $owner_id = identity::active_user()->id;
      foreach ($entries as $entry) {
        if (microtime(true) - $start > 0.5) {
          break;
        }

        // Look up the parent item for this entry.  By now it should exist, but if none was
        // specified, then this belongs as a child of the current item.
        $parent_entry = ORM::factory("folder_sync_entry", $entry->parent_id);
        if (!$parent_entry->loaded()) {
          $parent = ORM::factory("item", $task->get("item_id"));
        } else {
          $parent = ORM::factory("item", $parent_entry->item_id);
        }

        $name = basename($entry->path);
        $title = item::convert_filename_to_title($name);
        if ($entry->is_directory) {
          if(module::get_var("folder_sync", "skip_duplicates")) {
            $album_exists = ORM::factory("item")->where("type", "=", "album")
              ->where("name", "=", $name)->find();
          } else {
            $album_exists = null;
          }
          if($album_exists && $album_exists->loaded()) {
            // Skip adding of an album
            $entry->item_id = $album_exists->id;
          } else {
            $album = ORM::factory("item");
            $album->type = "album";
            $album->parent_id = $parent->id;
            $album->name = $name;
            $album->title = $title;
            $album->owner_id = $owner_id;
            $album->sort_order = $parent->sort_order;
            $album->sort_column = $parent->sort_column;
            $album->save();
            $entry->item_id = $album->id;
          }
        } else {
          try {
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            $entry_exists = 0;
            if(module::get_var("folder_sync", "skip_duplicates")) {
              $entry_exists = ORM::factory("folder_sync_entry")
                ->where("is_directory", "=", 0)
                ->where("item_id", "IS NOT", null)
                ->where("path", "=", $entry->path)
                ->find();
            }
            if ($entry_exists && $entry_exists->loaded()) {
              // skip adding an image
              $task->log("Entry exists: {$entry->path}");
              if(module::get_var("folder_sync", "process_updates")) {
                $task->log("Entry exists, processing update {$entry_exists->item_id}: {$entry->path}");
                if($entry_exists->md5 != $entry->md5) {
                  $item = ORM::factory("item", $entry_exists->item_id);
                  if($item->loaded()) {
                    $task->log("Entry exists, set data file {$item->id}: {$entry->path} ");
                    $item->set_data_file($entry->path);
                    $item->save();
                    $entry_exists->md5 = $entry->md5;
                    $entry_exists->save();
                  }
                }
              }
              $entry->item_id = 0;
              //$task->log("Skipping existing item: {$entry->path}");
            } elseif (in_array($extension, array("gif", "png", "jpg", "jpeg"))) {
              $photo = ORM::factory("item");
              $photo->type = "photo";
              $photo->parent_id = $parent->id;
              $photo->set_data_file($entry->path);
              $photo->name = $name;
              $photo->title = $title;
              $photo->owner_id = $owner_id;
              $photo->save();
              $entry->item_id = $photo->id;
            } else if (in_array($extension, array("flv", "mp4", "m4v"))) {
              $movie = ORM::factory("item");
              $movie->type = "movie";
              $movie->parent_id = $parent->id;
              $movie->set_data_file($entry->path);
              $movie->name = $name;
              $movie->title = $title;
              $movie->owner_id = $owner_id;
              $movie->save();
              $entry->item_id = $movie->id;
            } else {
              // This should never happen, because we don't add stuff to the list that we can't
              // process.  But just in, case.. set this to a non-null value so that we skip this
              // entry.
              $entry->item_id = 0;
              $task->log("Skipping unknown file type: {$entry->path}");
            }
          } catch (Exception $e) {
            // This can happen if a photo file is invalid, like a BMP masquerading as a .jpg
            $entry->item_id = 0;
            $task->log("Skipping invalid file: {$entry->path}, $e");
          }
        }

        $completed_files++;
        $entry->save();
      }
      $task->set("completed_files", $completed_files);
      $task->status = t("Adding photos / albums (%completed of %total)",
                        array("completed" => $completed_files,
                              "total" => $total_files));
      $task->percent_complete = $total_files ? 10 + 90 * ($completed_files / $total_files) : 100;
      break;

    case "done":
      batch::stop();
      $task->done = true;
      $task->state = "success";
      $task->percent_complete = 100;
      if(module::get_var("folder_sync", "skip_duplicates")) {
        db::build()
          ->delete("folder_sync_entries")
          ->where("task_id", "=", $task->id)
          ->where("item_id", "=", 0)
          ->execute();
      } else {
        db::build()
          ->delete("folder_sync_entries")
          ->where("task_id", "=", $task->id)
          ->execute();
      }
      message::info(t2("Successfully added one photo / album",
                       "Successfully added %count photos / albums",
                       $task->get("completed_files")));
    }
  }
}
