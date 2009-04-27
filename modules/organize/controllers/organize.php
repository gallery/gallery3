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
class Organize_Controller extends Controller {
  private static $_MICRO_THUMB_SIZE = 90;
  private static $_MICRO_THUMB_PADDING = 5;

  function index($item_id=1) {
    $item = ORM::factory("item", $item_id);
    $root = ($item->id == 1) ? $item : ORM::factory("item", 1);

    $v = new View("organize.html");
    $v->root = $root;
    $v->item = $item;
    $v->album_tree = $this->tree($item, $root);

    $v->edit_form = new View("organize_edit.html");
    $v->button_pane = new View("organize_button_pane.html");
 
    print $v;
  }

  function content($item_id) {
    $item = ORM::factory("item", $item_id);
    $width = $this->input->get("width");
    $height = $this->input->get("height");
    $offset = $this->input->get("offset", 0);
    $thumbsize = self::$_MICRO_THUMB_SIZE + 2 * self::$_MICRO_THUMB_PADDING;
    $page_size = ceil($width / $thumbsize) * ceil($height / $thumbsize);
 
    $v = new View("organize_thumb_grid.html");
    $v->children = $item->children($page_size, $offset);
    $v->thumbsize = self::$_MICRO_THUMB_SIZE;
    $v->padding = self::$_MICRO_THUMB_PADDING;
    $v->offset = $offset;

    print json_encode(array("count" => $v->children->count(),
                            "data" => $v->__toString()));
  }

  function header($item_id) {
    $item = ORM::factory("item", $item_id);

    print json_encode(array("title" => $item->title,
                            "description" => empty($item->description) ? "" : $item->description));
  }

  function tree($item, $parent) {
    $albums = ORM::factory("item")
      ->where(array("parent_id" => $parent->id, "type" => "album"))
      ->orderby(array("title" => "ASC"))
      ->find_all();

    $v = new View("organize_album.html");
    $v->album = $parent;
    $v->selected = $parent->id == $item->id;
    
    if ($albums->count()) {
      $v->album_icon = $parent->id == 1 || $v->selected ? "ui-icon-minus" : "ui-icon-plus";
    } else {
      $v->album_icon = "";
    }

    $v->children = "";
    foreach ($albums as $album) {
      $v->children .= $this->tree($item, $album);
    }
    return $v->__toString();
  }


  function runTask($task_id) {
    access::verify_csrf();

    $task = task::run($task_id);

    print json_encode(array("result" => $task->done ? $task->state : "in_progress",
                            "task" => array(
                              "id" => $task->id,
                              "percent_complete" => $task->percent_complete,
                              "status" => $task->status,
                              "state" => $task->state,
                              "done" => $task->done)));
  }

  function finishTask($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);

    if ($task->done) {
      $item = ORM::factory("item", (int)$task->get("target"));
      $type = $task->get("type");
      if ($type == "moveTo") {
        $task->status = t("Move to '%album' completed", array("album" => $item->title));
      } else if ($type == "rearrange") {
        try {
          $item->sort_column = "weight";
          $item->save();
          $task->status = t("Rearrange for '%album' completed", array("album" => $item->title));
        } catch (Exception $e) {
          $task->state = "error";
          $task->status = $e->getMessage();
        }
      }
      $task->save();
   }

    batch::stop();
    print json_encode(array("result" => "success",
                            "task" => array(
                              "id" => $task->id,
                              "percent_complete" => $task->percent_complete,
                              "status" => $task->status,
                              "state" => $task->state,
                              "done" => $task->done)));
  }
  
  function cancelTask($task_id) {
    access::verify_csrf();

    $task = ORM::factory("task", $task_id);

    if (!$task->done) {
      $task->done = 1;
      $task->state = "cancelled";
      $type = $task->get("type");
      if ($type == "moveTo") {
        $task->status = t("Move to album was cancelled prior to completion");
      } else if ($type == "rearrange") {
        $task->status = t("Rearrange album was cancelled prior to completion");
      }
      $task->save();     
    }

    batch::stop();
    print json_encode(array("result" => "success",
                            "task" => array(
                              "id" => $task->id,
                              "percent_complete" => $task->percent_complete,
                              "status" => $task->status,
                              "state" => $task->state,
                              "done" => $task->done)));
  }
  
  function moveStart($id) {
    access::verify_csrf();
    $items = $this->input->post("item");
    
    $item = ORM::factory("item", $id);

    $task_def = Task_Definition::factory()
      ->callback("organize_task::move")
      ->description(t("Move albums and photos to '%name'", array("name" => $item->title)))
      ->name(t("Move to '%name'", array("name" => $item->title)));
    $task = task::create($task_def, array("items" => $items, "position" => 0, "target" => $id,
                                          "type" => "moveTo",
                                          "batch" => ceil(count($items) * .1)));

    batch::start();
    print json_encode(array("result" => "started",
                            "task" => array(
                              "id" => $task->id,
                              "percent_complete" => $task->percent_complete,
                              "status" => $task->status,
                              "state" => $task->state,
                              "done" => $task->done)));
  }
  
  function rearrangeStart($id) {
    access::verify_csrf();
    $items = $this->input->post("item");
    
    $item = ORM::factory("item", $id);

    $task_def = Task_Definition::factory()
      ->callback("organize_task::rearrange")
      ->description(t("Rearrange the order of albums and photos"))
      ->name(t("Rearrange: %name", array("name" => $item->title)));
    $task = task::create($task_def, array("items" => $items, "position" => 0, "target" => $id,
                                          "type" => "rearrange",
                                          "batch" => ceil(count($items) * .1)));

    batch::start();
    print json_encode(array("result" => "started",
                            "task" => array(
                              "id" => $task->id,
                              "percent_complete" => $task->percent_complete,
                              "status" => $task->status,
                              "state" => $task->state,
                              "done" => $task->done)));
  }
}