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
class Tag_Controller_Rest_Tag extends Controller_Rest {
  /**
   * This resource represents a Model_Tag object.
   *
   * GET displays the tag (no parameters accepted).
   *   @see  Controller_Rest_Tag::get_entity()
   *
   * PUT can accept the following post parameters:
   *   entity
   *     Edit the tag
   *   @see  Controller_Rest_Tag::put_entity()
   *
   * DELETE removes the tag entirely (no parameters accepted).
   *   @see  Controller_Rest_Tag::delete()
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE a tag.
   */

  /**
   * GET the tag's entity.
   */
  static function get_entity($id, $params) {
    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    return $tag->as_array();
  }

  /**
   * PUT the tag's entity.  This edits the tag model, and is only for admins.
   */
  static function put_entity($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Add fields from a whitelist.
    foreach (array("name", "slug") as $field) {
      if (property_exists($params["entity"], $field)) {
        $tag->$field = $params["entity"]->$field;
      }
    }

    $tag->save();
  }

  /**
   * DELETE the tag.  This is only for admins.
   */
  static function delete($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    $tag->delete();
  }
}
