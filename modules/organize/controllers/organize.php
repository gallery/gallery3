<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
  function frame($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $v = new View("organize_frame.html");
    $v->album = $album;
    print $v;
  }

  function dialog($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    $v = new View("organize_dialog.html");
    $v->album = $album;
    print $v;
  }

  function tree($selected_album_id) {
    $root = ORM::factory("item", Input::instance()->post("root_id", 1));
    $selected_album = ORM::factory("item", $selected_album_id);
    access::required("view", $root);
    access::required("view", $selected_album);

    $tree = $this->_get_tree($root, $selected_album);
    json::reply($tree);
  }

  function album_info($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);

    $data = array(
      "sort_column" => $album->sort_column,
      "sort_order" => $album->sort_order,
      "children" => array());

    foreach ($album->viewable()->children() as $child) {
      $dims = $child->scale_dimensions(120);
      $data["children"][] = array(
        "id" => $child->id,
        "thumb_url" => $child->thumb_url(),
        "width" => $dims[1],
        "height" => $dims[0],
        "type" => $child->type,
        "title" => $child->title);
    }
    json::reply($data);
  }

  function reparent() {
    access::verify_csrf();

    $input = Input::instance();
    $new_parent = ORM::factory("item", $input->post("target_id"));
    access::required("edit", $new_parent);

    foreach (explode(",", $input->post("source_ids")) as $source_id) {
      $source = ORM::factory("item", $source_id);
      access::required("edit", $source->parent());

      $source->parent_id = $new_parent->id;
      $source->save();
    }
    json::reply(null);
  }

  function set_sort($album_id) {
    access::verify_csrf();
    $album = ORM::factory("item", $album_id);
    access::required("view", $album);
    access::required("edit", $album);

    foreach (array("sort_column", "sort_order") as $key) {
      if ($val = Input::instance()->post($key)) {
        $album->$key = $val;
      }
    }
    $album->save();

    json::reply(null);
  }

  function rearrange() {
    access::verify_csrf();

    $input = Input::instance();
    $target = ORM::factory("item", $input->post("target_id"));
    $album = $target->parent();
    access::required("edit", $album);

    if ($album->sort_column != "weight") {
      // Force all the weights into the current order before changing the order to manual
      $weight = 0;
      foreach ($album->children() as $child) {
        $child->weight = ++$weight;
        $child->save();
      }

      $album->sort_column = "weight";
      $album->sort_order = "ASC";
      $album->save();
    }

    $source_ids = explode(",", $input->post("source_ids"));
    $base_weight = $target->weight;
    if ($input->post("relative") == "after") {
      $base_weight++;
    }

    if ($source_ids) {
      // Make a hole the right size
      db::build()
        ->update("items")
        ->set("weight", db::expr("`weight` + " . count($source_ids)))
        ->where("parent_id", "=", $album->id)
        ->where("weight", ">=", $base_weight)
        ->execute();

      // Move all the source items to the right spots.
      for ($i = 0; $i < count($source_ids); $i++) {
        $source = ORM::factory("item", $source_ids[$i]);
        if ($source->parent_id = $album->id) {
          $source->weight = $base_weight + $i;
          $source->save();
        }
      }
    }
    json::reply(null);
  }

  private function _get_tree($item, $selected) {
    $tree = array();
    $children = $item->viewable()
      ->children(null, null, array(array("type", "=", "album")))
      ->as_array();
    foreach ($children as $child) {
      $node = array(
        "allowDrag" => false,
        "allowDrop" => access::can("edit", $child),
        "editable" => false,
        "expandable" => true,
        "id" => $child->id,
        "leaf" => false,
        "text" => $child->title,
        "nodeType" => "async");

      // If the child is in the selected path, open it now.  Else, mark it async.
      if ($child->contains($selected)) {
        $node["children"] = $this->_get_tree($child, $selected);
        $node["expanded"] = true;
      }
      $tree[] = $node;
    }
    return $tree;
  }
}
