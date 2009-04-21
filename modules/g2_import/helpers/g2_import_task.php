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
    }
  }

  static function import($task) {
    $start = microtime(true);
    g2_import::init();

    $stats = $task->get("stats");
    $total = $task->get("total");
    $completed = $task->get("completed");
    $i = $task->get("i");
    $mode = $task->get("mode");
    $queue = $task->get("queue");
    if (!isset($mode)) {
      $task->set("stats", $stats = g2_import::stats());
      $task->set("total", $total = array_sum(array_values($stats)));
      $completed = 0;
      $i = 0;
      $mode = 0;

      $root_g2_id = g2(GalleryCoreApi::getDefaultAlbumId());
      $root = ORM::factory("g2_map")->where("g2_id", $root_g2_id)->find();
      if (!$root->loaded) {
        $root->g2_id = $root_g2_id;
        $root->g3_id = 1;
        $root->save();
      }
    }

    $modes = array("groups", "users", "albums", "photos", "comments", "done");
    while (!$task->done && microtime(true) - $start < 1) {
      if ($i >= ($stats[$modes[$mode]] - 1)) {
        $i = 0;
        $mode++;
        $queue = array();
      }

      switch($modes[$mode]) {
      case "groups":
        if (!$i) {
          $task->set("queue", $queue = array_keys(g2(GalleryCoreApi::fetchGroupNames())));
        }
        g2_import::import_group($queue);
        $task->status = t(
          "Importing groups %count / %total", array("count" => $i, "total" => $stats["groups"]));
        break;

      case "users":
        if (!$i) {
          $task->set(
            "queue", $queue = array_keys(g2(GalleryCoreApi::fetchUsersForGroup(GROUP_EVERYBODY))));
        }
        g2_import::import_user($queue);
        $task->status = t(
          "Importing users %count / %total", array("count" => $i, "total" => $stats["users"]));
        break;

      case "albums":
        if (!$i) {
          $task->set("queue", $queue = g2(GalleryCoreApi::fetchAlbumTree()));
        }
        g2_import::import_album($queue);
        $task->set("queue", $queue);
        $task->status = t(
          "Importing albums %count / %total", array("count" => $i, "total" => $stats["albums"]));
        break;

      case "photos":
        if (empty($queue)) {
          $task->set("queue", $queue = g2_import::get_item_ids($task->get("last_id", 0)));
          $task->set("last_id", end($queue));
        }

        g2_import::import_item($queue);
        $task->status = t(
          "Importing photos %count / %total", array("count" => $i, "total" => $stats["photos"]));
        break;

      case "comments":
        $task->status = t("Importing comments %count / %total",
                          array("count" => $i, "total" => $stats["comments"]));
        break;

      case "done":
        $task->status = t("Import complete");
        $task->done = true;
        $task->state = "success";
        break;
      }

      $i++;
      if (!$task->done) {
        $completed++;
      }
    }

    $task->percent_complete = 100 * ($completed / $total);
    $task->set("completed", $completed);
    $task->set("mode", $mode);
    $task->set("i", $i);
    $task->set("queue", $queue);
  }
}
