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
class organize_task_Core {
  static function available_tasks() {
    // Return empty array so nothing appears in the maintenance screen
    return array();
  }

  static function run($task) {
    $context = unserialize($task->context);
    $taskType = $context["type"];

    try {
      $target = ORM::factory("item", $context["target"]);
      $total = count($context["items"]);
      $stop = min($total - $context["position"], $context["batch"]);
      $context["post_process"] = array();
      for ($offset = 0; $offset < $stop; $offset++) {
        $current_id = $context["position"] + $offset;
        $id = $context["items"][$current_id];
        switch ($taskType) {
        case "move":
          $source = ORM::factory("item", $id);
          core::move_item($source, $target);
          break;
        case "rearrange":
          Database::instance()
            ->query("Update {items} set weight = {$context["position"]} where id=$id;");
          break;
        case "rotateCcw":
        case "rotateCw":
          $item = ORM::factory("item", $id);
          if ($item->is_photo()) {
            $context["post_process"]["reload"][] =
              self::_do_rotation($item, $taskType == "rotateCcw" ? -90 : 90);
          }
          break;
        case "albumCover":
          $item = ORM::factory("item", $id);
          $item->make_album_cover();
          break;
        case "delete":
          $item = ORM::factory("item", $id);
          $item->delete();
          $context["post_process"]["remove"][] = array("id" => $id);
          break;
        default:
          throw new Exception("Task '$taskType' is not implmented");
        }
      }
      $context["position"] += $stop;
      $task->state = "success";
    } catch(Exception $e) {
      $task->status = $e->getMessage();
      $task->state = "error";
      $task->save();
      throw $e;
    }
    $task->context = serialize($context);
    $total = count($context["items"]);
    $task->percent_complete = $context["position"] / (float)$total * 100;
    $task->done = $context["position"] == $total || $task->state == "error";
  }

  private static function _do_rotation($item, $degrees) {
    // This code is copied from Quick_Controller::rotate
    graphics::rotate($item->file_path(), $item->file_path(), array("degrees" => $degrees));

    list($item->width, $item->height) = getimagesize($item->file_path());
    $item->resize_dirty= 1;
    $item->thumb_dirty= 1;
    $item->save();

    graphics::generate($item);

    $parent = $item->parent();
    if ($parent->album_cover_item_id == $item->id) {
      copy($item->thumb_path(), $parent->thumb_path());
      $parent->thumb_width = $item->thumb_width;
      $parent->thumb_height = $item->thumb_height;
      $parent->save();
    }
    list ($height, $width) = $item->adjust_thumb_size(90);
    $margin_top = (90 - $height) / 20;

    return array("src" => $item->thumb_url() . "?rnd=" . rand(),
                 "id" => $item->id,
                 "marginTop" => "{$margin_top}em", "width" => $width, "height" => $height);
  }
}