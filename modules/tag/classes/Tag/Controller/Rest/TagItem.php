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
class Tag_Controller_Rest_TagItem extends Controller_Rest {
  /**
   * This resource represents a pair of tag/item resources.  This is deprecated from 3.1,
   * but functionality is maintained for backward compatibility.
   *
   * GET displays the tag_item (no parameters accepted).
   *   @see  Controller_Rest_TagItem::get_entity()
   *
   * DELETE removes the tag from the item (no parameters accepted).
   *   @see  Controller_Rest_TagItem::delete()
   *
   * @see  Controller_Rest_TagItems::post_entity(), which can POST a tag_item (also deprecated).
   * @see  Controller_Rest_ItemTags::post_entity(), which can POST a tag_item (also deprecated).
   */

  /**
   * GET the tag_item resource.
   */
  static function get_entity($id, $params) {
    $ids = explode(",", $id);

    $tag  = ORM::factory("Tag",  Arr::get($ids, 0));
    $item = ORM::factory("Item", Arr::get($ids, 1));

    if (!$tag->loaded() || !$item->loaded() ||
        !$tag->has("items", $item) || !Access::can("view", $item)) {
      throw Rest_Exception::factory(404);
    }

    return array("tag" => Rest::url("tag", $tag->id), "item" => Rest::url("item", $item->id));
  }

  /**
   * DELETE the tag_item resource.  This removes the tag from the item.
   */
  static function delete($id, $params) {
    $ids = explode(",", $id);

    $tag  = ORM::factory("Tag",  Arr::get($ids, 0));
    $item = ORM::factory("Item", Arr::get($ids, 1));

    if (!$tag->loaded() || !$item->loaded() ||
        !$tag->has("items", $item) || !Access::can("edit", $item)) {
      throw Rest_Exception::factory(404);
    }

    $tag->remove("items", $item);
    $tag->save();
  }

  /**
   * Override Controller_Rest::action_get() to add the deprecated notice header.
   */
  public function action_get() {
    $this->response->headers("x-gallery-api-notice",
      "Deprecated from 3.1 - use of tag_item resource");
    return parent::action_get();
  }

  /**
   * Override Controller_Rest::action_delete() to add the deprecated notice header.
   */
  public function action_delete() {
    $this->response->headers("x-gallery-api-notice",
      "Deprecated from 3.1 - use of tag_item resource");
    return parent::action_delete();
  }
}
