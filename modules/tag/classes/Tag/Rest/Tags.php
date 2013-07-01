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
class Tag_Rest_Tags extends Rest {
  /**
   * This resource represents a Model_Tag.
   *
   * GET displays a tag (if id given)
   *   (no parameters)
   *
   * GET displays a collection of tags (no id given)
   *   name=<substring>
   *     Only return tags that start with this substring.
   *     This is typically used for tag autocomplete.
   *   order=count, order=name
   *     Return the tags in decreasing order by count ("count", typically used for tag
   *     clouds) or increasing order by name ("name", typically used for autocomplete
   *     or other alphabetic lists).  If the "name" parameter is also set, the default
   *     is "name"; otherwise, the default is "count".
   *
   * PUT
   *   entity
   *     Edit the tag
   *
   * POST
   *   entity
   *     Add a tag.  This is best used when also POSTing item relationships.  Otherwise, the
   *     tag will have 0 count, and Gallery may unexpectedly "clean it up" (i.e. delete it).
   *
   * DELETE removes the tag entirely (no parameters accepted).
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE a tag.
   */

  /**
   * GET the tag's entity.
   */
  public function get_entity() {
    if (empty($this->id)) {
      return null;
    }

    $tag = ORM::factory("Tag", $this->id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    $data = $tag->as_array();
    $data["web_url"] = $tag->abs_url();

    return $data;
  }

  /**
   * PUT the tag's entity.  This edits the tag model, and is only for admins.
   */
  public function put_entity() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $tag = ORM::factory("Tag", $this->id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Add fields from a whitelist.
    foreach (array("name", "slug") as $field) {
      if (property_exists($this->params["entity"], $field)) {
        $tag->$field = $this->params["entity"]->$field;
      }
    }

    $tag->save();
  }

  /**
   * DELETE the tag.  This is only for admins.
   */
  public function delete() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $tag = ORM::factory("Tag", $this->id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    $tag->delete();
  }

  /**
   * GET the members of the tags collection.
   */
  public function get_members() {
    if (!empty($this->id)) {
      return null;
    }

    $members = ORM::factory("Tag");

    if (isset($this->params["name"])) {
      $members->where("name", "LIKE", Database::escape_for_like($this->params["name"]) . "%");
      $default_order = "name";  // Useful for autocomplete
    } else {
      $default_order = "count"; // Useful for cloud
    }

    switch (Arr::get($this->params, "order", $default_order)) {
    case "name":
      $members->order_by("name", "ASC");

    case "count":  // default as set by Model_Tag::$_sorting - do nothing.
      break;

    default:
      throw Rest_Exception::factory(400, array("order" => "invalid"));
    }

    $this->members_info["count"] = $members->reset(false)->count_all();
    $members = $members
      ->limit($this->members_info["num"])
      ->offset($this->members_info["start"])
      ->find_all();

    $data = array();
    foreach ($members as $member) {
      $data[] = Rest::factory("tags", $member->id);
    }

    return $data;
  }

  /**
   * POST a tag's entity.  This generates a new tag model.
   */
  public function post_entity() {
    if (!empty($this->id)) {
      return null;
    }

    // The user must have some edit permission somewhere to create a tag.
    if (!Identity::active_user()->admin) {
      $query = DB::select()->from("access_caches")->and_where_open();
      foreach (Identity::active_user()->groups() as $group) {
        $query->or_where("edit_{$group->id}", "=", Access::ALLOW);
      }
      $has_any_edit_perm = $query->and_where_close()->execute()->count();
      if (!$has_any_edit_perm) {
        throw Rest_Exception::factory(403);
      }
    }

    // The name field is required.
    if (!property_exists($this->params["entity"], "name")) {
      throw Rest_Exception::factory(400, array("name" => "required"));
    }

    // See if we already have a tag with the same name.
    $tag = ORM::factory("Tag")->where("name", "=", $this->params["entity"]->name)->find();
    if ($new = !$tag->loaded()) {
      // New tag - add fields from a whitelist.
      foreach (array("name", "slug") as $field) {
        if (property_exists($this->params["entity"], $field)) {
          $tag->$field = $this->params["entity"]->$field;
        }
      }

      $tag->save();
    } else {
      $this->created = false;
    }

    // Success!
    $this->id = $tag->id;
  }
}
