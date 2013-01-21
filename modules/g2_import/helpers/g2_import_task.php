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
class g2_import_task_Core {
  static function available_tasks() {
    $version = '';
    g2_import::lower_error_reporting();
    if (g2_import::is_configured()) {
      g2_import::init();
      // Guard from common case where the import has been
      // completed and the original files have been removed.
      if (class_exists("GalleryCoreApi")) {
        $version = g2_import::version();
      }
    }
    g2_import::restore_error_reporting();

    if (g2_import::is_initialized()) {
      return array(Task_Definition::factory()
                   ->callback("g2_import_task::import")
                   ->name(t("Import from Gallery 2"))
                   ->description(
                     t("Gallery %version detected", array("version" => $version)))
                   ->severity(log::SUCCESS));
    }

    return array();
  }

  static function import($task) {
    g2_import::lower_error_reporting();

    $start = microtime(true);
    g2_import::init();

    $stats = $task->get("stats");
    $done = $task->get("done");
    $total = $task->get("total");
    $completed = $task->get("completed");
    $mode = $task->get("mode");
    $queue = $task->get("queue");
    if (!isset($mode)) {
      $stats = g2_import::g2_stats();
      $stats["items"] = $stats["photos"] + $stats["movies"];
      unset($stats["photos"]);
      unset($stats["movies"]);
      $stats["highlights"] = $stats["albums"];
      $task->set("stats", $stats);

      $task->set("total", $total = array_sum(array_values($stats)));
      $completed = 0;
      $mode = 0;

      $done = array();
      foreach (array_keys($stats) as $key) {
        $done[$key] = 0;
      }
      $task->set("done", $done);

      // Ensure G2 ACLs are compacted to speed up import.
      g2(GalleryCoreApi::compactAccessLists());
    }

    $modes = array("groups", "users", "albums", "items", "comments", "tags", "highlights", "done");
    while (!$task->done && microtime(true) - $start < 1.5) {
      if ($done[$modes[$mode]] == $stats[$modes[$mode]]) {
        // Nothing left to do for this mode.  Advance.
        $mode++;
        $task->set("last_id", 0);
        $queue = array();

        // Start the loop from the beginning again.  This way if we get to a mode that requires no
        // actions (eg, if the G2 comments module isn't installed) we won't try to do any comments
        // queries.. in the next iteration we'll just skip over that mode.
        if ($modes[$mode] != "done") {
          continue;
        }
      }

      switch($modes[$mode]) {
      case "groups":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_group_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }
        $log_message = g2_import::import_group($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing groups (%count of %total)",
          array("count" => $done["groups"] + 1, "total" => $stats["groups"]));
        break;

      case "users":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_user_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }
        $log_message = g2_import::import_user($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing users (%count of %total)",
          array("count" => $done["users"] + 1, "total" => $stats["users"]));
        break;

      case "albums":
        if (empty($queue)) {
          $g2_root_id = g2(GalleryCoreApi::getDefaultAlbumId());
          $tree = g2(GalleryCoreApi::fetchAlbumTree());
          $task->set("queue", $queue = array($g2_root_id => $tree));

          // Update the root album to reflect the Gallery2 root album.
          $root_album = item::root();
          g2_import::set_album_values(
            $root_album, g2(GalleryCoreApi::loadEntitiesById($g2_root_id)));
          $root_album->save();
        }
        $log_message = g2_import::import_album($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing albums (%count of %total)",
          array("count" => $done["albums"] + 1, "total" => $stats["albums"]));
        break;

      case "items":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_item_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }
        $log_message = g2_import::import_item($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing photos (%count of %total)",
          array("count" => $done["items"] + 1, "total" => $stats["items"]));
        break;

      case "comments":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_comment_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }
        $log_message = g2_import::import_comment($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing comments (%count of %total)",
          array("count" => $done["comments"] + 1, "total" => $stats["comments"]));

        break;

      case "tags":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_tag_item_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }
        $log_message = g2_import::import_tags_for_item($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing tags (%count of %total)",
          array("count" => $done["tags"] + 1, "total" => $stats["tags"]));

        break;

      case "highlights":
        if (empty($queue)) {
          $task->set("queue", $queue = g2(GalleryCoreApi::fetchAlbumTree()));
        }
        $log_message = g2_import::set_album_highlight($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Album highlights (%count of %total)",
          array("count" => $done["highlights"] + 1, "total" => $stats["highlights"]));

        break;

      case "done":
        $task->status = t("Import complete");
        $task->done = true;
        $task->state = "success";
        break;
      }

      if (!$task->done) {
        $done[$modes[$mode]]++;
        $completed++;
      }
    }

    $task->percent_complete = 100 * ($completed / $total);
    $task->set("completed", $completed);
    $task->set("mode", $mode);
    $task->set("queue", $queue);
    $task->set("done", $done);

    g2_import::restore_error_reporting();
  }
}
