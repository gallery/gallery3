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
class Tag_Hook_TagTask {

  static function available_tasks() {
    $tasks[] = TaskDefinition::factory()
      ->callback("Hook_TagTask::clean_up_tags")
      ->name(t("Clean up tags"))
      ->description(t("Correct tag counts and remove tags with no items"))
      ->severity(GalleryLog::SUCCESS);
    return $tasks;
  }

  /**
   * Fix up tag counts and delete any tags that have no associated items.
   * @param Model_Task the task
   */
  static function clean_up_tags($task) {
    $errors = array();
    try {
      $start = microtime(true);
      $last_tag_id = $task->get_data("last_tag_id", null);
      $current = 0;
      $total = 0;

      switch ($task->get_data("mode", "init")) {
      case "init":
        $task->set_data("total", ORM::factory("Tag")->count_all());
        $task->set_data("mode", "clean_up_tags");
        $task->set_data("completed", 0);
        $task->set_data("last_tag_id", 0);

      case "clean_up_tags":
        $completed = $task->get_data("completed");
        $total = $task->get_data("total");
        $last_tag_id = $task->get_data("last_tag_id");
        $tags = ORM::factory("Tag")->where("id", ">", $last_tag_id)->limit(25)->find_all();
        Log::instance()->add(Log::ERROR,print_r(Database::instance()->last_query(),1));
        while ($current < $total && microtime(true) - $start < 1 && $tag = $tags->current()) {
          $last_tag_id = $tag->id;
          $real_count = $tag->items->count_all();
          if ($tag->count != $real_count) {
            $tag->count = $real_count;
            if ($tag->count) {
              $task->log(
                "Fixing count for tag {$tag->name} (id: {$tag->id}, new count: {$tag->count})");
              $tag->save();
            } else {
              $task->log("Deleting empty tag {$tag->name} ({$tag->id})");
              $tag->delete();
            }
          }

          $completed++;
          $tags->next();
        }
        $task->percent_complete = $completed / $total * 100;
        $task->set_data("completed", $completed);
        $task->set_data("last_tag_id", $last_tag_id);
      }

      $task->status = t2("Examined %count tag", "Examined %count tags", $completed);

      if ($completed == $total) {
        $task->done = true;
        $task->state = "success";
        $task->percent_complete = 100;
      }
    } catch (Exception $e) {
      Log::instance()->add(Log::ERROR,(string)$e);
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