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
class item_ordered_members_rest_Core {
  static function get($request) {
    $item = rest::resolve($request->url);
    $ordered_members = array();
    foreach ($item->children() as $child) {
      $ordered_members[] = rest::url("item", $child);
    }

    return array(
      "url" => $request->url,
      "entity" => array("ordered_members" => $ordered_members));
  }

  static function put($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    // Verify that we're not adding or removing members this way
    if (count($request->params->ordered_members) != $item->children_count()) {
      throw new Rest_Exception("Bad Request", 400);
    }

    $ordered_members = array();
    foreach ($request->params->ordered_members as $url) {
      $member = rest::resolve($url);
      if ($member->parent_id != $item->id) {
        throw new Rest_Exception("Bad Request", 400);
      }
      $ordered_members[] = $member;
    }

    // Update all the weights.  This is a pretty inefficient way to do this if we're just changing
    // one or two elements, but it's easy.  We could optimize this by looking at the current order
    // and figuring out which elements have moved and then only changing those values.
    $i = 0;
    foreach ($ordered_members as $member) {
      $member->weight = $i++;
      $member->save();
    }
  }

  static function relationships($resource_type, $resource) {
    if ($resource_type == "item" && $resource->is_album()) {
      return array(
        "item_ordered_members" => array(
          "url" => rest::url("item_ordered_members", $resource)));
    }

    return array();
  }

  static function resolve($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item) || !$item->is_album()) {
      throw new Kohana_404_Exception();
    }
    return $item;
  }

  static function url($item) {
    return url::abs_site("rest/item_ordered_members/{$item->id}");
  }
}
