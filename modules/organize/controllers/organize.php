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
  function dialog($item_id) {
    $item = ORM::factory("item", $item_id);
    $root = $item->id == 1 ? $item : ORM::factory("item", 1);
    access::required("view", $item);
    access::required("edit", $item);

    $v = new View("organize_dialog.html");
    $v->title = $item->title;
    $parents = array();
    foreach ($item->parents() as $parent) {
      $parents[$parent->id] = 1;
    }
    $parents[$item->id] = 1;

    $v->album_tree = self::_tree($root, $parents);
    $v->micro_thumb_grid = self::_get_micro_thumb_grid($item, 0);
    print $v;
  }

  function content($item_id, $offset) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);
    access::required("edit", $item);
    print self::_get_micro_thumb_grid($item, $offset);
  }

  function move($target_id) {
    access::verify_csrf();

    $task_def = Task_Definition::factory()
      ->callback("Organize_Controller::move_task_handler")
      ->description(t("Move images"))
      ->name(t("Move Images"));
    $task = task::create($task_def, array("target_id" => $target_id,
                                          "source_ids" => $this->input->post("source_ids")));

    print json_encode(
      array("result" => "started",
            "status" => $task->status,
            "url" => url::site("organize/run/$task->id?csrf=" . access::csrf_token())));
  }

  function rearrange($target_id, $before) {
    access::verify_csrf();
    $target = ORM::factory("item", $target_id);
    $parent = $target->parent();
    access::required("view", $parent);
    access::required("edit", $parent);

    $task_def = Task_Definition::factory()
      ->callback("Organize_Controller::rearrange_task_handler")
      ->description(t("Rearrange Image"))
      ->name(t("Rearrange Images"));
    $task = task::create($task_def, array("target_id" => $target_id, "before" => $before,
                                          "parent_id" => $parent->id,
                                          "weight" => item::get_max_weight(),
                                          "total" => $parent->children_count(),
                                          "source_ids" => $this->input->post("source_ids")));

    print json_encode(
      array("result" => "started",
            "status" => $task->status,
            "url" => url::site("organize/run/$task->id?csrf=" . access::csrf_token())));
  }

  private static function _get_micro_thumb_grid($item, $offset) {
    $v = new View("organize_thumb_grid.html");
    $v->item = $item;
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
    foreach (array("tree", "content") as $data) {
      $value = $task->get($data, false);
      if ($value !== false) {
        $results[$data] = $value;
      }
    }
    print json_encode($results);
  }

  private static function _tree($item, $parents) {
    $v = new View("organize_tree.html");
    $v->album = $item;
    $keys = array_keys($parents);
    $v->selected = end($keys) == $item->id;
    $v->can_edit= access::can("edit", $item);
    $v->children = array();
    $v->album_icon = "gBranchEmpty";

    $albums = $item->children(null, 0, array("type" => "album"), array("title" => "ASC"));
    foreach ($albums as $album) {
      if (access::can("view", $album)) {
        $v->children[] = self::_tree($album, $parents);
      }
    }
    if (count($v->children)) {
      $v->album_icon = empty($parents[$item->id]) ? "ui-icon-plus" : "ui-icon-minus";
    }
    return $v;
  }

  static function move_task_handler($task) {
    $start = microtime(true);
    if ($task->percent_complete == 0) {
      batch::start();
    }

    $target = ORM::factory("item", $task->get("target_id"));
    $source_ids = $task->get("source_ids", array());
    $idx = $task->get("current", 0);
    $count = 0;
    for (; $idx < count($source_ids) && microtime(true) - $start < 0.5; $idx++) {
      item::move(ORM::factory("item", $source_ids[$idx]), $target);
      $count++;
    }
    $task->set("current", $idx);
    $task->percent_complete = (int)($idx / count($source_ids) * 100);
    $task->status = t2("Moved one file", "Moved %count files", $count);
    if ($task->percent_complete == 100) {
      batch::stop();
      $task->done = true;
      $task->state = "success";
      $parents = array();
      foreach ($target->parents() as $parent) {
        $parents[$parent->id] = 1;
      }
      $parents[$target->id] = 1;
      // @TODO do we want to set a flag and then generate them in the run method so we don't
      // potentially store large data items in the task?
      $task->set("tree", self::_tree(ORM::factory("item", 1), $parents)->__toString());
      $task->set("content", self::_get_micro_thumb_grid($target, 0)->__toString());
    }
  }

  static function rearrange_task_handler($task) {
    $phase = $task->get("phase", "before_drop");
    $source_ids = $task->get("source_ids");
    $parent = ORM::factory("item", $task->get("parent_id"));
    $weight = $task->get("weight");
    $target_id = $task->get("target_id");
    $is_before = $task->get("before") == "before";

    // @todo at some point if we allow drag from album tree this needs to be changed
    if ($phase == "dropping") {
      $children = ORM::factory("item")
        ->where("parent_id", $parent->id)
        ->where("weight < ", $weight)
        ->in("id", $source_ids)
        ->orderby(array($parent->sort_column => $parent->sort_order))
        ->find_all();
      if ($children->count() == 0) {
        $phase = "after_drop";
        $task->set("phase", $phase);
      }
    }
    if ($phase != "dropping") {
      $dropping = false;
      $children = ORM::factory("item")
        ->where("parent_id", $parent->id)
        ->where("weight < ", $weight)
        ->in("id", $source_ids, true)
        ->orderby(array($parent->sort_column => $parent->sort_order))
        ->find_all();
    }
    $completed = $task->get("completed", 0);

    $start = microtime(true);
    foreach ($children as $child) {
      $step = microtime(true);
      if (microtime(true) - $start > 0.5) {
        break;
      }
      if ($phase == "before_drop" && $child->id == $target_id && $is_before) {
        $task->set("dropping", true);
        $task->set("phase", "dropping");
        break;
      }
      Database::instance()->query(
        "UPDATE {items} SET `weight` =  " . item::get_max_weight() .
        " WHERE `id` = " . $child->id);

      $completed++;
      if ($phase == "before_drop" && $child->id == $task->get("target_id")) {
        $task->set("dropping", true);
        $task->set("phase", "dropping");
        break;
      }
    }
    if ($completed == $task->get("total")) {
      Database::instance()->query(
        "UPDATE {items} SET `sort_column` =  \"weight\"" .
        " WHERE `id` = " . $parent->id);
      module::event("album_rearrange", $parent);
      $task->done = true;
      $task->state = "success";
      $task->set("content", self::_get_micro_thumb_grid($parent, 0)->__toString());
      $task->percent_complete = 100;
    } else {
      $task->percent_complete = (int)(100 * $completed / $task->get("total"));
    }
    $task->set("completed", $completed);
  }
}
