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
class core_task_Core {
  static function available_tasks() {
    $dirty_count = graphics::find_dirty_images_query()->count();
    $tasks = array();
    $tasks[] = Task_Definition::factory()
                 ->callback("core_task::rebuild_dirty_images")
                 ->name(t("Rebuild Images"))
                 ->description($dirty_count ?
                               t2("You have one out of date photo",
                                  "You have %count out of date photos",
                                  $dirty_count)
                               : t("All your photos are up to date"))
      ->severity($dirty_count ? log::WARNING : log::SUCCESS);

    $tasks[] = Task_Definition::factory()
                 ->callback("core_task::update_l10n")
                 ->name(t("Update translations"))
                 ->description(t("Download new and updated translated strings"))
      ->severity(log::SUCCESS);

    return $tasks;
  }

  /**
   * Task that rebuilds all dirty images.
   * @param Task_Model the task
   */
  static function rebuild_dirty_images($task) {
    $result = graphics::find_dirty_images_query();
    $remaining = $result->count();
    $completed = $task->get("completed", 0);

    $i = 0;
    foreach ($result as $row) {
      $item = ORM::factory("item", $row->id);
      if ($item->loaded) {
        graphics::generate($item);
      }

      $completed++;
      $remaining--;

      if (++$i == 2) {
        break;
      }
    }

    $task->status = t2("Updated: 1 image. Total: %total_count.",
                       "Updated: %count images. Total: %total_count.",
                       $completed,
                       array("total_count" => ($remaining + $completed)));

    if ($completed + $remaining > 0) {
      $task->percent_complete = (int)(100 * $completed / ($completed + $remaining));
    } else {
      $task->percent_complete = 100;
    }

    $task->set("completed", $completed);
    if ($remaining == 0) {
      $task->done = true;
      $task->state = "success";
      site_status::clear("graphics_dirty");
    }
  }

  static function update_l10n(&$task) {
    $start = microtime(true);
    $dirs = $task->get("dirs");
    $files = $task->get("files");
    $cache = $task->get("cache", array());
    $i = 0;

    switch ($task->get("mode", "init")) {
    case "init":  // 0%
      $dirs = array("core", "modules", "themes", "installer");
      $files = array();
      $task->set("mode", "find_files");
      $task->status = t("Finding files");
      break;

    case "find_files":  // 0% - 10%
      while (($dir = array_pop($dirs)) && microtime(true) - $start < 0.5) {
        if (basename($dir) == "tests") {
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

    case "scan_files": // 10% - 90%
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

      $task->percent_complete = 10 + 80 * ($total_files - count($files)) / $total_files;
      if (empty($files)) {
        $task->set("mode", "fetch_updates");
        $task->status = t("Fetching updates");
        $task->percent_complete = 90;
      }
      break;

    case "fetch_updates":  // 90% - 100%
      l10n_client::fetch_updates();
      $task->done = true;
      $task->state = "success";
      $task->status = t("Translations installed/updated");
      $task->percent_complete = 100;
    }

    $task->set("files", $files);
    $task->set("dirs", $dirs);
    $task->set("cache", $cache);
  }
}