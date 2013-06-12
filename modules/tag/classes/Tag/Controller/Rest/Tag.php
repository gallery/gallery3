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
class Tag_Hook_Rest_Tag {
  static function get($request) {
    $tag = Rest::resolve($request->url);
    $tag_items = array();
    foreach ($tag->items->viewable()->order_by("item.id")->find_all() as $item) {
      $tag_items[] = Rest::url("tag_item", $tag, $item);
    }

    return array(
      "url" => $request->url,
      "entity" => $tag->as_array(),
      "relationships" => array(
        "items" => array(
          "url" => Rest::url("tag_items", $tag),
          "members" => $tag_items)));
  }

  static function put($request) {
    // Who can we allow to edit a tag name?  If we allow anybody to do it then any logged in
    // user can rename all your tags to something offensive.  Right now limit renaming to admins.
    if (!Identity::active_user()->admin) {
      Access::forbidden();
    }
    $tag = Rest::resolve($request->url);
    if (isset($request->params->entity->name)) {
      $tag->name = $request->params->entity->name;
      $tag->save();
    }
  }

  static function delete($request) {
    // Restrict deleting tags to admins.  Otherwise, a logged in user can do great harm to an
    // install.
    if (!Identity::active_user()->admin) {
      Access::forbidden();
    }
    $tag = Rest::resolve($request->url);
    $tag->delete();
  }

  static function relationships($resource_type, $resource) {
    switch ($resource_type) {
    case "item":
      $tags = array();
      foreach ($resource->tags->find_all() as $tag) {
        $tags[] = Rest::url("tag_item", $tag, $resource);
      }
      return array(
        "tags" => array(
          "url" => Rest::url("item_tags", $resource),
          "members" => $tags));
    }
  }

  static function resolve($id) {
    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    return $tag;
  }

  static function url($tag) {
    return URL::abs_site("rest/tag/{$tag->id}");
  }
}
