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
class gallery_task_Core {
  static function available_tasks() {
    $dirty_count = graphics::find_dirty_images_query()->count_records();
    $tasks = array();
    $tasks[] = Task_Definition::factory()
                 ->callback("gallery_task::rebuild_dirty_images")
                 ->name(t("Rebuild Images"))
                 ->description($dirty_count ?
                               t2("You have one out of date photo",
                                  "You have %count out of date photos",
                                  $dirty_count)
                               : t("All your photos are up to date"))
      ->severity($dirty_count ? log::WARNING : log::SUCCESS);

    $tasks[] = Task_Definition::factory()
                 ->callback("gallery_task::update_l10n")
                 ->name(t("Update translations"))
                 ->description(t("Download new and updated translated strings"))
      ->severity(log::SUCCESS);

    $tasks[] = Task_Definition::factory()
                 ->callback("gallery_task::file_cleanup")
                 ->name(t("Remove old files"))
                 ->description(t("Remove files from the logs and tmp directory"))
      ->severity(log::SUCCESS);
    return $tasks;
  }

  /**
   * Task that rebuilds all dirty images.
   * @param Task_Model the task
   */
  static function rebuild_dirty_images($task) {
    $errors = array();
    try {
      $result = graphics::find_dirty_images_query()->select("id")->execute();
      $total_count = $task->get("total_count", $result->count());
      $mode = $task->get("mode", "init");
      if ($mode == "init") {
        $task->set("total_count", $total_count);
        $task->set("mode", "process");
        batch::start();
      }

      $completed = $task->get("completed", 0);
      $ignored = $task->get("ignored", array());

      $i = 0;
      foreach ($result as $row) {
        if (array_key_exists($row->id, $ignored)) {
          continue;
        }

        $item = ORM::factory("item", $row->id);
        if ($item->loaded()) {
          try {
            graphics::generate($item);
            $completed++;

            $errors[] = t("Successfully rebuilt images for '%title'",
                          array("title" => html::purify($item->title)));
          } catch (Exception $e) {
            $errors[] = t("Unable to rebuild images for '%title'",
                          array("title" => html::purify($item->title)));
            $errors[] = (string)$e;
            $ignored[$item->id] = 1;
          }
        }

        if (++$i == 2) {
          break;
        }
      }

      $task->status = t2("Updated: 1 image. Total: %total_count.",
                         "Updated: %count images. Total: %total_count.",
                         $completed,
                         array("total_count" => $total_count));

      if ($completed < $total_count) {
        $task->percent_complete = (int)(100 * ($completed + count($ignored)) / $total_count);
      } else {
        $task->percent_complete = 100;
      }

      $task->set("completed", $completed);
      $task->set("ignored", $ignored);
      if ($task->percent_complete == 100) {
        $task->done = true;
        $task->state = "success";
        batch::stop();
        site_status::clear("graphics_dirty");
      }
    } catch (Exception $e) {
      Kohana_Log::add("error",(string)$e);
      $task->done = true;
      $task->state = "error";
      $task->status = $e->getMessage();
      $errors[] = (string)$e;
    }
    if ($errors) {
      $task->log($errors);
    }
  }

  static function update_l10n($task) {
    $errors = array();
    try {
      $start = microtime(true);
      $data = Cache::instance()->get("update_l10n_cache:{$task->id}");
      if ($data) {
        list($dirs, $files, $cache, $num_fetched) = unserialize($data);
      }
      $i = 0;

      switch ($task->get("mode", "init")) {
      case "init":  // 0%
        $dirs = array("gallery", "modules", "themes", "installer");
        $files = $cache = array();
        $num_fetched = 0;
        $task->set("mode", "find_files");
        $task->status = t("Finding files");
        break;

      case "find_files":  // 0% - 10%
        while (($dir = array_pop($dirs)) && microtime(true) - $start < 0.5) {
          if (in_array(basename($dir), array("tests", "lib"))) {
            continue;
          }

          foreach (glob(DOCROOT . "$dir/*") as $path) {
            $relative_path = str_replace(DOCROOT, "", $path);
            if (is_dir($path)) {
              $dirs[] = $relative_path;
            } else {
              $files[] = $relative_path;
            }
          }
        }

        $task->status = t2("Finding files: found 1 file",
                           "Finding files: found %count files", count($files));

        if (!$dirs) {
          $task->set("mode", "scan_files");
          $task->set("total_files", count($files));
          $task->status = t("Scanning files");
          $task->percent_complete = 10;
        }
        break;

      case "scan_files": // 10% - 70%
        while (($file = array_pop($files)) && microtime(true) - $start < 0.5) {
          $file = DOCROOT . $file;
          switch (pathinfo($file, PATHINFO_EXTENSION)) {
          case "php":
            l10n_scanner::scan_php_file($file, $cache);
            break;

          case "info":
            l10n_scanner::scan_info_file($file, $cache);
            break;
          }
        }

        $total_files = $task->get("total_files");
        $task->status = t2("Scanning files: scanned 1 file",
                           "Scanning files: scanned %count files", $total_files - count($files));

        $task->percent_complete = 10 + 60 * ($total_files - count($files)) / $total_files;
        if (empty($files)) {
          $task->set("mode", "fetch_updates");
          $task->status = t("Fetching updates");
          $task->percent_complete = 70;
        }
        break;

      case "fetch_updates":  // 70% - 100%
        // Send fetch requests in batches until we're done
        $num_remaining = l10n_client::fetch_updates($num_fetched);
        if ($num_remaining) {
          $total = $num_fetched + $num_remaining;
          $task->percent_complete = 70 + 30 * ((float) $num_fetched / $total);
        } else {
          $task->done = true;
          $task->state = "success";
          $task->status = t("Translations installed/updated");
          $task->percent_complete = 100;
        }
      }

      if (!$task->done) {
        Cache::instance()->set("update_l10n_cache:{$task->id}",
                               serialize(array($dirs, $files, $cache, $num_fetched)));
      } else {
        Cache::instance()->delete("update_l10n_cache:{$task->id}");
      }
    } catch (Exception $e) {
      Kohana_Log::add("error",(string)$e);
      $task->done = true;
      $task->state = "error";
      $task->status = $e->getMessage();
      $errors[] = (string)$e;
    }
    if ($errors) {
      $task->log($errors);
    }
  }

  /**
   * Task that removes old files from var/logs and var/tmp.
   * @param Task_Model the task
   */
  static function file_cleanup($task) {
    $errors = array();
    try {
      $start = microtime(true);
      $data = Cache::instance()->get("file_cleanup_cache:{$task->id}");
      $files = $data ? unserialize($data) : array();
      $i = 0;
      $current = 0;
      $total = 0;

      switch ($task->get("mode", "init")) {
      case "init":
        $threshold = time() - 1209600; // older than 2 weeks
        foreach(array("logs", "tmp") as $dir) {
          $dir = VARPATH . $dir;
          if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
              if ($file[0] == ".") {
                continue;
              }

              if (filemtime("$dir/$file") <= $threshold) {
                $files[] = "$dir/$file";
              }
            }
          }
        }
        $task->set("mode", "delete_files");
        $task->set("current", 0);
        $task->set("total", count($files));
        Cache::instance()->set("file_cleanup_cache:{$task->id}", serialize($files));
        if (count($files) == 0) {
          break;
        }

      case "delete_files":
        $current = $task->get("current");
        $total = $task->get("total");
        while ($current < $total && microtime(true) - $start < 1) {
          @unlink($files[$current]);
          $task->log(t("%file removed", array("file" => $files[$current++])));
        }
        $task->percent_complete = $current / $total * 100;
        $task->set("current", $current);
      }

      $task->status = t2("Removed: 1 file. Total: %total_count.",
                         "Removed: %count files. Total: %total_count.",
                         $current, array("total_count" => $total));

      if ($total == $current) {
        $task->done = true;
        $task->state = "success";
        $task->percent_complete = 100;
      }
    } catch (Exception $e) {
      Kohana_Log::add("error",(string)$e);
      $task->done = true;
      $task->state = "error";
      $task->status = $e->getMessage();
      $errors[] = (string)$e;
    }
    if ($errors) {
      $task->log($errors);
    }
  }
}