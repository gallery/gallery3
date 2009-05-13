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
      $paths = array_keys(unserialize(module::get_var("server_add", "authorized_paths")));
      $path = $paths[$context["next_path"]];
      if (!empty($context["files"][$path])) {
        $file = $context["files"][$path][$context["position"]];
        $parent = ORM::factory("item", $file["parent_id"]);
        access::required("server_add", $parent);
        access::required("add", $parent);
        if (!$parent->is_album()) {
          throw new Exception("@todo BAD_ALBUM");
        }

        $name = $file["name"];
        if ($file["type"] == "album") {
          $album = ORM::factory("item")
            ->where("name", $name)
            ->where("parent_id", $parent->id)
            ->find();
          if (!$album->loaded) {
            $album = album::create($parent, $name, $name, null, user::active()->id);
          }
          // Now that we have a new album. Go through the remaining files to import and change the
          // parent_id of any file that has the same relative path as this album's path.
          $album_path = "{$file['path']}/$name";
          for ($idx = $context["position"] + 1; $idx < count($context["files"][$path]); $idx++) {
            if (strpos($context["files"][$path][$idx]["path"], $album_path) === 0) {
              $context["files"][$path][$idx]["parent_id"] = $album->id;
            }
          }
        } else {
          $extension = strtolower(substr(strrchr($name, '.'), 1));
          $source_path = "$path{$file['path']}/$name";
          if (in_array($extension, array("flv", "mp4"))) {
            $movie = movie::create($parent, $source_path, $name, $name,
                                   null, user::active()->id);
          } else {
            $photo = photo::create($parent, $source_path, $name, $name,
                                   null, user::active()->id);
          }
        }

        $context["counter"]++;
        if (++$context["position"] >= count($context["files"][$path])) {
          $context["next_path"]++;
          $context["position"] = 0;
        }
      } else {
        $context["next_path"]++;
      }
    } catch(Exception $e) {
      $context["errors"][$path] = $e->getMessage();
    }
    $task->context = serialize($context);
    $task->state = "success";
    $task->percent_complete = ($context["counter"] / (float)$context["total"]) * 100;
    $task->done = $context["counter"] == (float)$context["total"];
  }
}