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
class G2Import_Hook_G2ImportTask {
  static function available_tasks() {
    $version = '';
    G2Import::lower_error_reporting();
    if (G2Import::is_configured()) {
      G2Import::init();
      // Guard from common case where the import has been
      // completed and the original files have been removed.
      if (class_exists("GalleryCoreApi")) {
        $version = G2Import::version();
      }
    }
    G2Import::restore_error_reporting();

    if (G2Import::is_initialized()) {
      return array(TaskDefinition::factory()
                   ->callback("Hook_G2ImportTask::import")
                   ->name(t("Import from Gallery 2"))
                   ->description(
                     t("Gallery %version detected", array("version" => $version)))
                   ->severity(GalleryLog::SUCCESS));
    }

    return array();
  }

  static function import($task) {
    G2Import::lower_error_reporting();

    $start = microtime(true);
    G2Import::init();

    $stats = $task->get_data("stats");
    $done = $task->get_data("done");
    $total = $task->get_data("total");
    $completed = $task->get_data("completed");
    $mode = $task->get_data("mode");
    $queue = $task->get_data("queue");
    if (!isset($mode)) {
      $stats = G2Import::g2_stats();
      $stats["items"] = $stats["photos"] + $stats["movies"];
      unset($stats["photos"]);
      unset($stats["movies"]);
      $stats["highlights"] = $stats["albums"];
      $task->set_data("stats", $stats);

      $task->set_data("total", $total = array_sum(array_values($stats)));
      $completed = 0;
      $mode = 0;

      $done = array();
      foreach (array_keys($stats) as $key) {
        $done[$key] = 0;
      }
      $task->set_data("done", $done);

      // Ensure G2 ACLs are compacted to speed up import.
      g2(GalleryCoreApi::compactAccessLists());
    }

    $modes = array("groups", "users", "albums", "items", "comments", "tags", "highlights", "done");
    while (!$task->done && microtime(true) - $start < 1.5) {
      if ($done[$modes[$mode]] == $stats[$modes[$mode]]) {
        // Nothing left to do for this mode.  Advance.
        $mode++;
        $task->set_data("last_id", 0);
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
          $task->set_data("queue", $queue = G2Import::get_group_ids($task->get_data("last_id", 0)));
          $task->set_data("last_id", end($queue));
        }
        $log_message = G2Import::import_group($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing groups (%count of %total)",
          array("count" => $done["groups"] + 1, "total" => $stats["groups"]));
        break;

      case "users":
        if (empty($queue)) {
          $task->set_data("queue", $queue = G2Import::get_user_ids($task->get_data("last_id", 0)));
          $task->set_data("last_id", end($queue));
        }
        $log_message = G2Import::import_user($queue);
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
          $task->set_data("queue", $queue = array($g2_root_id => $tree));

          // Update the root album to reflect the Gallery2 root album.
          $root_album = Item::root();
          G2Import::set_album_values(
            $root_album, g2(GalleryCoreApi::loadEntitiesById($g2_root_id)));
          $root_album->save();
        }
        $log_message = G2Import::import_album($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing albums (%count of %total)",
          array("count" => $done["albums"] + 1, "total" => $stats["albums"]));
        break;

      case "items":
        if (empty($queue)) {
          $task->set_data("queue", $queue = G2Import::get_item_ids($task->get_data("last_id", 0)));
          $task->set_data("last_id", end($queue));
        }
        $log_message = G2Import::import_item($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing photos (%count of %total)",
          array("count" => $done["items"] + 1, "total" => $stats["items"]));
        break;

      case "comments":
        if (empty($queue)) {
          $task->set_data("queue", $queue = G2Import::get_comment_ids($task->get_data("last_id", 0)));
          $task->set_data("last_id", end($queue));
        }
        $log_message = G2Import::import_comment($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing comments (%count of %total)",
          array("count" => $done["comments"] + 1, "total" => $stats["comments"]));

        break;

      case "tags":
        if (empty($queue)) {
          $task->set_data("queue", $queue = G2Import::get_tag_item_ids($task->get_data("last_id", 0)));
          $task->set_data("last_id", end($queue));
        }
        $log_message = G2Import::import_tags_for_item($queue);
        if ($log_message) {
          $task->log($log_message);
        }
        $task->status = t(
          "Importing tags (%count of %total)",
          array("count" => $done["tags"] + 1, "total" => $stats["tags"]));

        break;

      case "highlights":
        if (empty($queue)) {
          $task->set_data("queue", $queue = g2(GalleryCoreApi::fetchAlbumTree()));
        }
        $log_message = G2Import::set_album_highlight($queue);
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
    $task->set_data("completed", $completed);
    $task->set_data("mode", $mode);
    $task->set_data("queue", $queue);
    $task->set_data("done", $done);

    G2Import::restore_error_reporting();
  }
}
