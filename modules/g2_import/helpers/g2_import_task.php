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
class g2_import_task_Core {
  static function available_tasks() {
    if (g2_import::is_configured()) {
      g2_import::init();
      return array(Task_Definition::factory()
                   ->callback("g2_import_task::import")
                   ->name(t("Import from Gallery 2"))
                   ->description(
                     t("Gallery %version detected", array("version" => g2_import::version())))
                   ->severity(log::SUCCESS));
    } else {
      return array();
    }
  }

  static function import($task) {
    $start = microtime(true);
    g2_import::init();

    $stats = $task->get("stats");
    $done = $task->get("done");
    $total = $task->get("total");
    $completed = $task->get("completed");
    $mode = $task->get("mode");
    $queue = $task->get("queue");
    if (!isset($mode)) {
      $stats = g2_import::stats();
      $stats["items"] = $stats["photos"] + $stats["movies"];
      unset($stats["photos"]);
      unset($stats["movies"]);
      $task->set("stats", $stats);

      $task->set("total", $total = array_sum(array_values($stats)));
      $completed = 0;
      $mode = 0;

      $done = array();
      foreach (array_keys($stats) as $key) {
        $done[$key] = 0;
      }
      $task->set("done", $done);

      $root_g2_id = g2(GalleryCoreApi::getDefaultAlbumId());
      $root = ORM::factory("g2_map")->where("g2_id", $root_g2_id)->find();
      if (!$root->loaded) {
        $root->g2_id = $root_g2_id;
        $root->g3_id = 1;
        $root->save();
      }
    }

    $modes = array("groups", "users", "albums", "items", "comments", "tags", "done");
    while (!$task->done && microtime(true) - $start < 1.5) {
      if ($done[$modes[$mode]] == $stats[$modes[$mode]]) {
        // Nothing left to do for this mode.  Advance.
        $mode++;
        $task->set("last_id", 0);
        $queue = array();
      }

      switch($modes[$mode]) {
      case "groups":
        if (empty($queue)) {
          $task->set("queue", $queue = array_keys(g2(GalleryCoreApi::fetchGroupNames())));
        }
        g2_import::import_group($queue);
        $task->status = t(
          "Importing groups (%count of %total)",
          array("count" => $done["groups"] + 1, "total" => $stats["groups"]));
        break;

      case "users":
        if (empty($queue)) {
          $task->set(
            "queue", $queue = array_keys(g2(GalleryCoreApi::fetchUsersForGroup(GROUP_EVERYBODY))));
        }
        g2_import::import_user($queue);
        $task->status = t(
          "Importing users (%count of %total)",
          array("count" => $done["users"] + 1, "total" => $stats["users"]));
        break;

      case "albums":
        if (empty($queue)) {
          $task->set("queue", $queue = g2(GalleryCoreApi::fetchAlbumTree()));
        }
        g2_import::import_album($queue);
        $task->status = t(
          "Importing albums (%count of %total)",
          array("count" => $done["albums"] + 1, "total" => $stats["albums"]));
        break;

      case "items":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_item_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }

        g2_import::import_item($queue);
        $task->status = t(
          "Importing photos (%count of %total)",
          array("count" => $done["items"] + 1, "total" => $stats["items"]));
        break;

      case "comments":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_comment_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }
        g2_import::import_comment($queue);
        $task->status = t(
          "Importing comments (%count of %total)",
          array("count" => $done["comments"] + 1, "total" => $stats["comments"]));

        break;

      case "tags":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_tag_item_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }
        g2_import::import_tags_for_item($queue);
        $task->status = t(
          "Importing tags (%count of %total)",
          array("count" => $done["tags"] + 1, "total" => $stats["tags"]));

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
  }
}
