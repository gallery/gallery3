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
class server_add_task_Core {
  static function available_tasks() {
    // Return empty array so nothing appears in the maintenance screen
    return array();
  }

  static function add_from_server($task) {
    $context = unserialize($task->context);
    try {
      $parent = ORM::factory("item", $context["item_id"]);
      access::required("server_add", $parent);
      if (!$parent->is_album()) {
        throw new Exception("@todo BAD_ALBUM");
      }

      $paths = unserialize(module::get_var("server_add", "authorized_paths"));
      $path = $context["paths"][$context["next"]];

      $path_valid = false;
      $item_path = "";
      foreach (array_keys($paths) as $valid_path) {
        if ($path_valid = strpos($path, $valid_path) === 0) {
          $item_path = substr($path, strlen($valid_path));
          $item_path = explode("/", ltrim($item_path,"/"));
          $source_path = $valid_path;
          break;
        }
      }
      if (empty($path_valid)) {
        throw new Exception("@todo BAD_PATH");
      }
      for ($i = 0; $i < count($item_path); $i++) {
        $name = $item_path[$i];
        $source_path .= "/$name";
        if (is_link($source_path) || !is_readable($source_path)) {
          kohana::show_404();
        }
        $pathinfo = pathinfo($source_path);
        set_time_limit(30);
        if (is_dir($source_path)) {
          $album = ORM::factory("item")
            ->where("name", $name)
            ->where("parent_id", $parent->id)
            ->find();
          if (!$album->loaded) {
            $album = album::create($parent, $name, $name, null, user::active()->id);
          }
          $parent = $album;
        } else if (in_array($pathinfo["extension"], array("flv", "mp4"))) {
          $movie = movie::create($parent, $source_path, basename($source_path),
                                 basename($source_path), null, user::active()->id);
        } else {
          $photo = photo::create($parent, $source_path, basename($source_path),
                                 basename($source_path), null, user::active()->id);
        }
      }
    } catch(Exception $e) {
      $context["errors"][$path] = $e->getMessage();
    }
    $task->done = (++$context["next"]) >= count($context["paths"]);
    $task->context = serialize($context);
    $task->state = "success";
    $task->percent_complete = ($context["next"] / (float)count($context["paths"])) * 100;
  }

}