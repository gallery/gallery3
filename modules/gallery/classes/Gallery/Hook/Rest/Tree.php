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
class tree_rest_Core {
  /**
   * The tree is rooted in a single item and can have modifiers which adjust what data is shown
   * for items inside the given tree, up to the depth that you want.  The entity for this resource
   * is a series of items.
   *
   *  depth=<number>
   *    Only traverse this far down into the tree.  If there are more albums
   *    below this depth, provide RESTful urls to other tree resources in
   *    the members section.  Default is infinite.
   *
   *  type=<album|photo|movie>
   *    Restrict the items displayed to the given type.  Default is all types.
   *
   *  fields=<comma separated list of field names>
   *    In the entity section only return these fields for each item.
   *    Default is all fields.
   */
  static function get($request) {
    $item = rest::resolve($request->url);
    access::required("view", $item);

    $query_params = array();
    $p = $request->params;
    $where = array();
    if (isset($p->type)) {
      $where[] = array("type", "=", $p->type);
      $query_params[] = "type={$p->type}";
    }

    if (isset($p->depth)) {
      $lowest_depth = $item->level + $p->depth;
      $where[] = array("level", "<=", $lowest_depth);
      $query_params[] = "depth={$p->depth}";
    }

    $fields = array();
    if (isset($p->fields)) {
      $fields = explode(",", $p->fields);
      $query_params[] = "fields={$p->fields}";
    }

    $entity = array(array("url" => rest::url("item", $item),
                           "entity" => $item->as_restful_array($fields)));
    $members = array();
    foreach ($item->viewable()->descendants(null, null, $where) as $child) {
      $entity[] = array("url" => rest::url("item", $child),
                        "entity" => $child->as_restful_array($fields));
      if (isset($lowest_depth) && $child->level == $lowest_depth) {
        $members[] = url::merge_querystring(rest::url("tree", $child), $query_params);
      }
    }

    $result = array(
      "url" => $request->url,
      "entity" => $entity,
      "members" => $members,
      "relationships" => rest::relationships("tree", $item));
    return $result;
  }

  static function resolve($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }
    return $item;
  }

  static function url($item) {
    return url::abs_site("rest/tree/{$item->id}");
  }
}
