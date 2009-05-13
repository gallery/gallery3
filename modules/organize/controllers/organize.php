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

  function startTask($operation, $id) {
    access::verify_csrf();
    $items = $this->input->post("item");
    
    $item = ORM::factory("item", $id);

    $definition = $this->_getOperationDefinition($item, $operation);

    $task_def = Task_Definition::factory()
      ->callback("organize_task::run")
      ->description($definition["description"])
      ->name($definition["name"]);
    $task = task::create($task_def, array("items" => $items, "position" => 0, "target" => $id,
                                          "type" => $definition["type"],
                                          "batch" => ceil(count($items) * .1)));
    // @todo If there is only one item then call task_run($task->id); Maybe even change js so
    // we can call finish as well.
    batch::start();
    print json_encode(array("result" => "started",
                            "runningMsg" => $definition["runningMsg"],
                            "pauseMsg" => "<div class=\"gWarning\">{$definition['pauseMsg']}</div>",
                            "resumeMsg" => "<div class=\"gWarning\">{$definition['resumeMsg']}</div>",
                            "task" => array("id" => $task->id,
                                            "percent_complete" => $task->percent_complete,
                                            "type" => $task->get("type"),
                                            "status" => $task->status,
                                            "state" => $task->state,
                                            "done" => $task->done)));
  }

  function runTask($task_id) {
    access::verify_csrf();

    $task = task::run($task_id);

    print json_encode(array("result" => $task->done ? $task->state : "in_progress",
                            "task" => array("id" => $task->id,
                                            "percent_complete" => $task->percent_complete,
                                            "type" => $task->get("type"),
                                            "post_process" => $task->get("post_process"),
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
      switch ($type) {
      case "albumCover":
        $task->status = t("Album cover set for '%album'", array("album" => $item->title));
        break;
      case "delete":
        $task->status = t("Selection deleted");
        break;
      case "move":
        $task->status = t("Move to '%album' completed", array("album" => $item->title));
        break;
      case "rearrange":
        try {
          $item->sort_column = "weight";
          $item->save();
          $task->status = t("Rearrange for '%album' completed", array("album" => $item->title));
        } catch (Exception $e) {
          $task->state = "error";
          $task->status = $e->getMessage();
        }
        break;
      case "rotateCcw":
      case "rotateCw":
        $task->status = t("Rotation completed");
        break;
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
      switch ($type) {
      case "move":
        $task->status = t("Move to album was cancelled prior to completion");
        break;
      case "rearrange":
         $task->status = t("Rearrange album was cancelled prior to completion");
      case "rotateCcw":
      case "rotateCw":
        $task->status = t("Rotation was cancelled prior to completion");
        break;
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
  
  private function _getOperationDefinition($item, $operation) {
    switch ($operation) {
    case "move":
      return array("description" =>
                     t("Move albums and photos to '%name'", array("name" => $item->title)),
                   "name" => t("Move to '%name'", array("name" => $item->title)),
                   "type" => "move",
                   "runningMsg" => t("Move in progress"),
                   "pauseMsg" => t("The move operation was paused"),
                   "resumeMsg" => t("The move operation was resumed"));
      break;
    case "rearrange":
      return array("description" => t("Rearrange the order of albums and photos"),
                   "name" => t("Rearrange: %name", array("name" => $item->title)),
                   "type" => "rearrange",
                   "runningMsg" => t("Rearrange in progress"),
                   "pauseMsg" => t("The rearrange operation was paused"),
                   "resumeMsg" => t("The rearrange operation was resumed"));
      break;
    case "rotateCcw":
      return array("description" => t("Rotate the selected photos counter clockwise"),
                   "name" => t("Rotate images in %name", array("name" => $item->title)),
                   "type" => "rotateCcw",
                   "runningMsg" => t("Rotate Counter Clockwise in progress"),
                   "pauseMsg" => t("The rotate operation was paused"),
                   "resumeMsg" => t("The rotate operation was resumed"));
      break;
    case "rotateCw":
      return array("description" => t("Rotate the selected photos clockwise"),
                   "name" => t("Rotate images in %name", array("name" => $item->title)),
                   "type" => "rotateCw",
                   "runningMsg" => t("Rotate Clockwise in progress"),
                   "pauseMsg" => t("The rotate operation was paused"),
                   "resumeMsg" => t("The rotate operation was resumed"));
      break;
    case "delete":
      return array("description" => t("Delete selected photos and albums"),
                   "name" => t("Delete images in %name", array("name" => $item->title)),
                   "type" => "delete",
                   "runningMsg" => t("Delete images in progress"),
                   "pauseMsg" => t("The delete operation was paused"),
                   "resumeMsg" => t("The delete operation was resumed"));
      break;
    case "albumCover":
      return array("description" => t("Reset Album Cover"),
                   "name" => t("Reset Album cover for %name", array("name" => $item->title)),
                   "type" => "albumCover",
                   "runningMsg" => t("Reset Album Cover in progress"),
                   "pauseMsg" => t("Reset album cover was paused"),
                   "resumeMsg" => t("Reset album cover was resumed"));
      break;
    default:
      throw new Exception("Operation '$operation' is not implmented");
    }
  }
}