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
class Organize_Controller_Organize extends Controller {
  public function action_frame() {
    $album_id = $this->request->arg(0, "digit");
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("edit", $album);

    $v = new View("organize/frame.html");
    $v->album = $album;
    $this->response->body($v);
  }

  public function action_dialog() {
    $album_id = $this->request->arg(0, "digit");
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("edit", $album);

    $v = new View("organize/dialog.html");
    $v->album = $album;
    $this->response->body($v);
  }

  public function action_tree() {
    $selected_album_id = $this->request->arg(0, "digit");
    $root_id = Arr::get($this->request->post(), "root_id", Item::root()->id);
    $selected_album = ORM::factory("Item", $selected_album_id);
    $root = ORM::factory("Item", $root_id);
    Access::required("view", $root);
    Access::required("view", $selected_album);

    $tree = $this->_get_tree($root, $selected_album);
    $this->response->json($tree);
  }

  public function action_album_info() {
    $album_id = $this->request->arg(0, "digit");
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);

    $data = array(
      "sort_column" => $album->sort_column,
      "sort_order" => $album->sort_order,
      "editable" => Access::can("edit", $album),
      "title" => (string)HTML::clean($album->title),
      "children" => array());

    foreach ($album->children->viewable()->find_all() as $child) {
      $dims = $child->scale_dimensions(120);
      $data["children"][] = array(
        "id" => $child->id,
        "thumb_url" => $child->has_thumb() ? $child->thumb_url() : null,
        "width" => $dims[1],
        "height" => $dims[0],
        "type" => $child->type,
        "title" => (string)HTML::clean($child->title));
    }
    $this->response->json($data);
  }

  public function action_reparent() {
    Access::verify_csrf();

    $new_parent = ORM::factory("Item", $this->request->post("target_id"));
    Access::required("view", $new_parent);
    Access::required("edit", $new_parent);

    foreach (explode(",", $this->request->post("source_ids")) as $source_id) {
      $source = ORM::factory("Item", $source_id);
      if (!$source->loaded()) {
        continue;
      }
      Access::required("view", $source->parent);
      Access::required("edit", $source->parent);

      if ($source->contains($new_parent) || $source->id == $new_parent->id) {
        // Can't move an item into its own hierarchy.  Silently skip this,
        // since the UI shouldn't even allow this operation.
        continue;
      }

      $source->parent_id = $new_parent->id;
      $source->save();
    }
    $this->response->json(null);
  }

  public function action_set_sort() {
    $album_id = $this->request->arg(0, "digit");
    Access::verify_csrf();
    $album = ORM::factory("Item", $album_id);
    Access::required("view", $album);
    Access::required("edit", $album);

    foreach (array("sort_column", "sort_order") as $key) {
      if ($val = $this->request->post($key)) {
        $album->$key = $val;
      }
    }
    $album->save();

    $this->response->json(null);
  }

  public function action_rearrange() {
    Access::verify_csrf();

    $target = ORM::factory("Item", $this->request->post("target_id"));
    if (!$target->loaded()) {
      $this->response->json(null);
      return;
    }

    $album = $target->parent;
    Access::required("edit", $album);

    if ($album->sort_column != "weight") {
      // Force all the weights into the current order before changing the order to manual
      // @todo: consider making this a trigger in the Model_Item.
      Item::resequence_child_weights($album);
      $album->sort_column = "weight";
      $album->sort_order = "ASC";
      $album->save();
    }

    $source_ids = explode(",", $this->request->post("source_ids"));
    $base_weight = $target->weight;
    if ($this->request->post("relative") == "after") {
      $base_weight++;
    }

    if ($source_ids) {
      // Make a hole the right size
      DB::update("items")
        ->set(array("weight" => DB::expr("`weight` + " . count($source_ids))))
        ->where("parent_id", "=", $album->id)
        ->where("weight", ">=", $base_weight)
        ->execute();

      // Move all the source items to the right spots.
      for ($i = 0; $i < count($source_ids); $i++) {
        $source = ORM::factory("Item", $source_ids[$i]);
        if ($source->parent_id == $album->id) {
          $source->weight = $base_weight + $i;
          $source->save();
        }
      }
    }
    $this->response->json(null);
  }

  public function action_delete() {
    Access::verify_csrf();

    foreach (explode(",", $this->request->post("item_ids")) as $item_id) {
      $item = ORM::factory("Item", $item_id);
      if (Access::can("edit", $item)) {
        $item->delete();
      }
    }

    $this->response->json(null);
  }

  public function action_tag() {
    Access::verify_csrf();

    foreach (explode(",", $this->request->post("item_ids")) as $item_id) {
      $item = ORM::factory("Item", $item_id);
      if (Access::can("edit", $item)) {
        // Assuming the user can view/edit the current item, loop
        // through each tag that was submitted and apply it to
        // the current item.
        foreach (explode(",", $this->request->post("tag_names")) as $tag_name) {
          $tag_name = trim($tag_name);
          if ($tag_name) {
            Tag::add($item, $tag_name);
          }
        }
      }
    }

    $this->response->json(null);
  }

  protected function _get_tree($item, $selected) {
    $tree = array();
    $children = $item->children
      ->viewable()
      ->where("type", "=", "album")
      ->find_all()
      ->as_array();
    foreach ($children as $child) {
      $node = array(
        "allowDrag" => false,
        "allowDrop" => Access::can("edit", $child),
        "editable" => false,
        "expandable" => false,
        "id" => $child->id,
        "leaf" => $child->children->where("type", "=", "album")->count_all() == 0,
        "text" => (string)HTML::clean($child->title),
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
