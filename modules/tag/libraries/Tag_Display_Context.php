<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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

class Tag_Display_Context_Core extends Display_Context {
  protected function __construct() {
    parent::__construct("tag");
  }

  function display_context($item) {
    $tag = $this->get("tag");

    $where = array(array("type", "!=", "album"));

    $position = $this->get_position($tag, $item, $where);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) = $tag->items(3, $position - 2, $where);
    } else {
      $previous_item = null;
      list ($next_item) = $tag->items(1, $position, $where);
    }

    return array("position" =>$position,
                 "previous_item" => $previous_item,
                 "next_item" =>$next_item,
                 "sibling_count" => $tag->items_count($where),
                 "parents" => $this->bread_crumb($item));
  }

  function bread_crumb($item) {
    $tag = $this->get("tag");
    return array(item::root(), $this->dynamic_item($this->get("title"),
                                                      "tag/{$tag->id}/" . urlencode($tag->name) . "?show={$item->id}"));
  }

  /**
   * Find the position of the given item in the tag collection.  The resulting
   * value is 1-indexed, so the first child in the album is at position 1.
   *
   * @param Tag_Model  $tag
   * @param Item_Model $item
   * @param array      $where an array of arrays, each compatible with ORM::where()
   */
  private function get_position($tag, $item, $where=array()) {
    return ORM::factory("item")
      ->viewable()
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->where("items_tags.tag_id", "=", $tag->id)
      ->where("items.id", "<=", $item->id)
      ->merge_where($where)
      ->order_by("items.id")
      ->count_all();
  }
}
