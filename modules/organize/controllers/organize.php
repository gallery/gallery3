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
class Organize_Controller extends Controller {
  function dialog($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $v = new View("organize_dialog.html");
    $v->album = $album;
    $v->album_tree = self::_tree($album);
    $v->micro_thumb_grid = self::_get_micro_thumb_grid($album, 0);
    print $v;
  }

  function album($album_id, $offset) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    print json_encode(
      array("grid" => self::_get_micro_thumb_grid($album, $offset)->__toString(),
            "sort_column" => $album->sort_column,
            "sort_order" => $album->sort_order));
  }

  function move_to($album_id) {
    access::verify_csrf();

    $task_def = Task_Definition::factory()
      ->callback("Organize_Controller::move_task_handler");
    $task = task::create($task_def, array("album_id" => $album_id,
                                          "source_ids" => $this->input->post("source_ids")));

    print json_encode(
      array("result" => "started",
            "status" => $task->status,
            "url" => url::site("organize/run/$task->id?csrf=" . access::csrf_token())));
  }

  function rearrange($target_id, $before_or_after) {
    access::verify_csrf();

    $target = ORM::factory("item", $target_id);
    $parent = $target->parent();
    access::required("view", $parent);
    access::required("edit", $parent);

    $task_def = Task_Definition::factory()
      ->callback("Organize_Controller::rearrange_task_handler");
    $task = task::create(
      $task_def,
      array("target_id" => $target_id,
            "album_id" => $parent->id,
            "before_or_after" => $before_or_after,
            "source_ids" => $this->input->post("source_ids")));

    print json_encode(
      array("result" => "started",
            "status" => $task->status,
            "url" => url::site("organize/run/$task->id?csrf=" . access::csrf_token())));
  }

  function sort_order($album_id, $col, $dir) {
    access::verify_csrf();

    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $options = album::get_sort_order_options();
    if (!isset($options[$col])) {
      return;
    }

    $album->sort_column = $col;
    $album->sort_order = $dir;
    $album->save();

    print json_encode(
      array("grid" => self::_get_micro_thumb_grid($album, 0)->__toString(),
            "sort_column" => $album->sort_column,
            "sort_order" => $album->sort_order));
  }

  private static function _get_micro_thumb_grid($album, $offset) {
    $v = new View("organize_thumb_grid.html");
    $v->album = $album;
    $v->offset = $offset;
    return $v;
  }

  /**
   * Run the task
   */
  function run($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);
    if (!$task->loaded || $task->owner_id != user::active()->id) {
      access::forbidden();
    }

    $task = task::run($task_id);
    $results = array("done" => $task->done, "status" => $task->status,
                     "percent_complete" => $task->percent_complete);

    $album = ORM::factory("item", $task->get("album_id"));
    $result = array(
      "done" => $task->done,
      "status" => $task->status,
      "percent_complete" => $task->percent_complete);
    if ($task->done) {
      $result["tree"] = self::_tree($album)->__toString();
      $result["grid"] = self::_get_micro_thumb_grid($album, 0)->__toString();
    }
    print json_encode($result);
  }

  private static function _tree($album) {
    $v = new View("organize_tree.html");
    $v->parents = $album->parents();
    $v->album = $album;
    return $v;
  }

  static function move_task_handler($task) {
    $start = microtime(true);
    if ($task->percent_complete == 0) {
      batch::start();
    }

    $target_album = ORM::factory("item", $task->get("album_id"));
    $source_ids = $task->get("source_ids", array());
    $idx = $task->get("current", 0);
    $count = 0;
    for (; $idx < count($source_ids) && microtime(true) - $start < 0.5; $idx++) {
      item::move(ORM::factory("item", $source_ids[$idx]), $target_album);
      $count++;
    }
    $task->set("current", $idx);
    $task->percent_complete = (int)($idx / count($source_ids) * 100);
    if ($task->percent_complete == 100) {
      batch::stop();
      $task->done = true;
      $task->state = "success";
      $parents = array();
      foreach ($target_album->parents() as $parent) {
        $parents[$parent->id] = 1;
      }
      $parents[$target_album->id] = 1;
    }
  }

  static function rearrange_task_handler($task) {
    $start = microtime(true);
    $mode = $task->get("mode", "init");

    if ($task->percent_complete == 0) {
      batch::start();
    }
    while (microtime(true) - $start < 1.5) {
      switch ($mode) {
      case "init":
        $album = ORM::factory("item", $task->get("album_id"));
        if ($album->sort_column != "weight") {
          $mode = "convert-to-weight-order";
        } else {
          $mode = "find-insertion-point";
        }
        break;

      case "convert-to-weight-order":
        $i = 0;
        $album = ORM::factory("item", $task->get("album_id"));
        foreach ($album->children() as $child) {
          // Do this directly in the database to avoid sending notifications
          Database::Instance()->update("items", array("weight" => ++$i), array("id" => $child->id));
        }
        $album->sort_column = "weight";
        $album->sort_order = "ASC";
        $album->save();
        $mode = "find-insertion-point";
        $task->percent_complete = 25;
        break;

      case "find-insertion-point":
        $album = ORM::factory("item", $task->get("album_id"));
        $target_weight = $album->weight;

        if ($task->get("before_or_after") == "after") {
          $target_weight++;
        }
        $task->set("target_weight", $target_weight);
        $task->percent_complete = 40;
        $mode = "make-a-hole";
        break;

      case "make-a-hole":
        $target_weight = $task->get("target_weight");
        $source_ids = $task->get("source_ids");
        $count = count($source_ids);
        $album_id = $task->get("album_id");
        Database::Instance()->query(
          "UPDATE {items} " .
          "SET `weight` = `weight` + $count " .
          "WHERE `weight` >= $target_weight AND `parent_id` = {$album_id}");

        $mode = "insert-source-items";
        $task->percent_complete = 80;
        break;

      case "insert-source-items":
        $target_weight = $task->get("target_weight");
        foreach ($source_ids as $source_id) {
          Database::Instance()->update(
            "items", array("weight" => $target_weight++), array("id" => $source_id));
        }
        $mode = "done";
        break;

      case "done":
        $album = ORM::factory("item", $task->get("album_id"));
        module::event("album_rearrange", $album);
        batch::stop();
        $task->done = true;
        $task->state = "success";
        $task->percent_complete = 100;
        break;
      }
    }

    $task->set("mode", $mode);
  }
}
