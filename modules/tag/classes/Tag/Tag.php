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
class tag_Core {
  /**
   * Associate a tag with an item.  Create the tag if it doesn't already exist.
   *
   * @todo Write test.
   *
   * @param Item_Model $item an item
   * @param string     $tag_name a tag name
   * @return Tag_Model
   * @throws Exception("@todo {$tag_name} WAS_NOT_ADDED_TO {$item->id}")
   */
  static function add($item, $tag_name) {
    if (empty($tag_name)) {
      throw new exception("@todo MISSING_TAG_NAME");
    }

    $tag = ORM::factory("tag")->where("name", "=", $tag_name)->find();
    if (!$tag->loaded()) {
      $tag->name = $tag_name;
    }

    $tag->add($item);
    return $tag->save();
  }

  /**
   * Return the N most popular tags.
   *
   * @return ORM_Iterator of Tag_Model in descending tag count order
   */
  static function popular_tags($count) {
    $count = max($count, 1);
    return ORM::factory("tag")
      ->order_by("count", "DESC")
      ->limit($count)
      ->find_all();
  }

  /**
   * Return a rendering of the cloud for the N most popular tags.
   *
   * @param integer $count the number of tags
   * @return View
   */
  static function cloud($count) {
    $tags = tag::popular_tags($count)->as_array();
    if ($tags) {
      $cloud = new View("tag_cloud.html");
      $cloud->max_count = $tags[0]->count;
      if (!$cloud->max_count) {
        return;
      }
      usort($tags, array("tag", "sort_by_name"));
      $cloud->tags = $tags;
      return $cloud;
    }
  }

  static function sort_by_name($tag1, $tag2) {
    return strcasecmp($tag1->name, $tag2->name);
  }

  /**
   * Return all the tags for a given item.
   * @return array
   */
  static function item_tags($item) {
    return ORM::factory("tag")
      ->join("items_tags", "tags.id", "items_tags.tag_id", "left")
      ->where("items_tags.item_id", "=", $item->id)
      ->find_all();
  }

  /**
   * Return all the items for a given tag.
   * @return array
   */
  static function tag_items($tag) {
    return ORM::factory("item")
      ->join("items_tags", "items_tags.item_id", "items.id", "left")
      ->where("items_tags.tag_id", "=", $tag->id)
      ->find_all();
  }

  static function get_add_form($item) {
    $form = new Forge("tags/create/{$item->id}", "", "post", array("id" => "g-add-tag-form", "class" => "g-short-form"));
    $label = $item->is_album() ?
      t("Add tag to album") :
      ($item->is_photo() ? t("Add tag to photo") : t("Add tag to movie"));

    $group = $form->group("add_tag")->label("Add Tag");
    $group->input("name")->label($label)->rules("required")->id("name");
    $group->hidden("item_id")->value($item->id);
    $group->submit("")->value(t("Add Tag"));
    return $form;
  }

  static function get_delete_form($tag) {
    $form = new Forge("admin/tags/delete/$tag->id", "", "post", array("id" => "g-delete-tag-form"));
    $group = $form->group("delete_tag")
      ->label(t("Really delete tag %tag_name?", array("tag_name" => $tag->name)));
    $group->submit("")->value(t("Delete Tag"));
    return $form;
  }

  /**
   * Delete all tags associated with an item
   */
  static function clear_all($item) {
    db::build()
      ->update("tags")
      ->set("count", db::expr("`count` - 1"))
      ->where("count", ">", 0)
      ->where("id", "IN", db::build()->select("tag_id")->from("items_tags")->where("item_id", "=", $item->id))
      ->execute();
    db::build()
      ->delete("items_tags")
      ->where("item_id", "=", $item->id)
      ->execute();
  }

  /**
   * Remove all items from a tag
   */
  static function remove_items($tag) {
    db::build()
      ->delete("items_tags")
      ->where("tag_id", "=", $tag->id)
      ->execute();
    $tag->count = 0;
    $tag->save();
  }

  /**
   * Get rid of any tags that have no associated items.
   */
  static function compact() {
    // @todo There's a potential race condition here which we can solve by adding a lock around
    // this and all the cases where we create/update tags.  I'm loathe to do that since it's an
    // extremely rare case.
    db::build()->delete("tags")->where("count", "=", 0)->execute();
  }

  /**
   * Find the position of the given item in the tag collection.  The resulting
   * value is 1-indexed, so the first child in the album is at position 1.
   *
   * @param Tag_Model  $tag
   * @param Item_Model $item
   * @param array      $where an array of arrays, each compatible with ORM::where()
   */
  static function get_position($tag, $item, $where=array()) {
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