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
class Tag_Controller_Rest_ItemTags extends Controller_Rest {
  /**
   * This resource represents a collection of tag resources on a specified item.
   *
   * GET can accept the following query parameters:
   *   name=<substring>
   *     Only return tags that start with this substring.
   *     This is typically used for tag autocomplete.
   *   order=count, order=name
   *     Return the tags in decreasing order by count ("count", typically used for tag
   *     clouds) or increasing order by name ("name", typically used for autocomplete
   *     or other alphabetic lists).  If the "name" parameter is also set, the default
   *     is "name"; otherwise, the default is "count".
   *   @see  Controller_Rest_ItemTags::get_members()
   *
   * PUT can accept the following post parameters:
   *   members
   *     Replace the collection of tags on the item with this list
   *   @see  Controller_Rest_ItemTags::put_members()
   *
   * POST can accept the following post parameters:
   *   members
   *     Add the list of tags to the item.  Unlike PUT, this only *adds* tags.
   *     Since there is no entity function, this is only accessible using relationships.
   *   @see  Controller_Rest_ItemTags::post_members()
   *
   * DELETE removes the all tags from the item (no parameters accepted).
   *   @see  Controller_Rest_ItemTags::delete()
   *
   * RELATIONSHIPS: "item_tags" is the "tags" relationship of an "item" resource.
   *
   * Deprecated features: POST a "tag_item" entity.  This is deprecated from 3.1,
   * but functionality is maintained for backward compatibility.
   */

  /**
   * GET the tag members of the item_tags resource.
   * @see  Controller_Rest_Tags::get_members().
   */
  static function get_members($id, $params) {
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);

    $members = $item->tags
      ->limit(Arr::get($params, "num", static::$default_params["num"]))
      ->offset(Arr::get($params, "start", static::$default_params["start"]));

    if (isset($params["name"])) {
      $members->where("name", "LIKE", Database::escape_for_like($params["name"]) . "%");
      $default_order = "name";  // Useful for autocomplete
    } else {
      $default_order = "count"; // Useful for cloud
    }

    switch (Arr::get($params, "order", $default_order)) {
    case "count":
      $members->order_by("count", "DESC");
      break;

    case "name":
      $members->order_by("name", "ASC");
      break;

    default:
      throw Rest_Exception::factory(400, array("order" => "invalid"));
    }

    $data = array();
    foreach ($members->find_all() as $member) {
      $data[] = array("tag", $member->id);
    }

    return $data;
  }

  /**
   * PUT the tag members of the item_tags resource.  This replaces the tag list with the one given.
   * @see  TagEvent::item_edit_form_completed(), which does a similar task.
   */
  static function put_members($id, $params) {
    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    // Check if all the members have valid types and ids, and build our array of names.
    $tag_names = array();
    foreach ($params["members"] as $member) {
      list ($m_type, $m_id, $m_params) = Rest::resolve($member);
      if ($m_type != "tag") {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }

      $tag = ORM::factory("Tag", $m_id);
      if (!$tag->loaded()) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }

      $tag_names[] = $tag->name;
    }

    // Clear all tags from the item, then add the new set.
    Tag::clear_all($item);
    foreach ($tag_names as $tag_name) {
      Tag::add($item, $tag_name);
    }
    Tag::compact();
  }

  /**
   * POST tag members of the item_tags resource.  Unlike PUT, this only *adds* tags to the item.
   */
  static function post_members($id, $params) {
    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    // Check if all the members have valid types and ids, and build our array of names.
    $tag_names = array();
    foreach ($params["members"] as $member) {
      list ($m_type, $m_id, $m_params) = Rest::resolve($member);
      if ($m_type != "tag") {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }

      $tag = ORM::factory("Tag", $m_id);
      if (!$tag->loaded()) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }

      $tag_names[] = $tag->name;
    }

    // Add the tags to the item.
    foreach ($tag_names as $tag_name) {
      Tag::add($item, $tag_name);
    }
    Tag::compact();
  }

  /**
   * DELETE removes all tags from the item.
   */
  static function delete($id, $params) {
    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    Tag::clear_all($item);
  }

  /**
   * Return the relationship established by item_tags.  This adds "tags"
   * as a relationship of an "item" resource.
   */
  static function relationships($type, $id, $params) {
    return ($type == "item") ? array("tags" => array("item_tags", $id)) : null;
  }

  /**
   * POST a tag_item.  This feature is deprecated in v3.1, and is here to maintain
   * backward-compatibility with v3.0.
   */
  static function post_entity($id, $params) {
    list ($t_type, $t_id, $t_params) = Rest::resolve($params["entity"]->tag);
    list ($i_type, $i_id, $i_params) = Rest::resolve($params["entity"]->item);

    if (($t_type != "tag") || ($i_type != "item")) {
      throw Rest_Exception::factory(404);
    }

    $tag  = ORM::factory("Tag",  $t_id);
    $item = ORM::factory("Item", $i_id);


    Access::required("edit", $item);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    Tag::add($item, $tag->name);

    return array("tag_item", "$t_id,$i_id");
  }

  /**
   * Overload Controller_Rest::action_post() to block access unless they've sent a
   * well-formed tag_item entity POST.  If so, add the deprecated header and carry on.
   */
  public function action_post() {
    if (($entity = $this->request->post("entity")) &&
        property_exists($entity, "tag") &&
        property_exists($entity, "item") &&
        !$this->request->post("members") &&
        !$this->request->post("relationships")) {
      $this->response->headers("x-gallery-api-notice",
        "Deprecated from 3.1 - POSTing a tag_item resource to tag_items or item_tags");
      return parent::action_post();
    }

    throw Rest_Exception::factory(400, array("method" => "invalid"));
  }
}
