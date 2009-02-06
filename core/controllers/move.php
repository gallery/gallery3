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
class Move_Controller extends Controller {
  public function browse($source_id) {
    $source = ORM::factory("item", $source_id);
    access::required("edit", $source);

    $view = new View("move_browse.html");
    $view->source = $source;
    $view->tree = $this->_get_tree_html($source, ORM::factory("item", 1));
    print $view;
  }

  public function save($source_id) {
    access::verify_csrf();
    $source = ORM::factory("item", $source_id);
    access::required("edit", $source);
    $target = ORM::factory("item", $this->input->post("target_id"));
    access::required("edit", $target);
    $source->move_to($target);

    // If the target has no cover item, make this it.
    if ($target->album_cover_item_id == null)  {
      $target->album_cover_item_id =
        $source->is_album() ? $source->album_cover_item_id : $source->id;
      $target->save();
      graphics::generate($target);
    }

    print json_encode(
      array("result" => "success",
            "location" => url::site("albums/{$target->id}")));
  }

  public function show_sub_tree($source_id, $target_id) {
    $source = ORM::factory("item", $source_id);
    $target = ORM::factory("item", $target_id);
    access::required("edit", $source);
    access::required("view", $target);

    print $this->_get_tree_html($source, $target);
  }

  private function _get_tree_html($source, $target) {
    $view = new View("move_tree.html");
    $view->source = $source;
    $view->parent = $target;
    $view->children = ORM::factory("item")
      ->viewable()
      ->where("type", "album")
      ->where("parent_id", $target->id)
      ->find_all();
    return $view;
  }

}
