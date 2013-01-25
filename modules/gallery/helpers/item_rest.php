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
class item_rest_Core {
  /**
   * For items that are collections, you can specify the following additional query parameters to
   * query the collection.  You can specify them in any combination.
   *
   *   scope=direct
   *     Only return items that are immediately under this one
   *   scope=all
   *     Return items anywhere under this one
   *
   *   name=<substring>
   *     Only return items where the name contains this substring
   *
   *   random=true
   *     Return a single random item
   *
   *   type=<comma separate list of photo, movie or album>
   *     Limit the type to types in this list, eg: "type=photo,movie".
   *     Also limits the types returned in the member collections (same behaviour as item_rest).
   */
  static function get($request) {
    $item = rest::resolve($request->url);
    access::required("view", $item);

    $p = $request->params;
    if (isset($p->random)) {
      $orm = item::random_query()->offset(0)->limit(1);
    } else {
      $orm = ORM::factory("item")->viewable();
    }

    if (empty($p->scope)) {
      $p->scope = "direct";
    }

    if (!in_array($p->scope, array("direct", "all"))) {
      throw new Rest_Exception("Bad Request", 400);
    }

    if ($p->scope == "direct") {
      $orm->where("parent_id", "=", $item->id);
    } else {
      $orm->where("left_ptr", ">", $item->left_ptr);
      $orm->where("right_ptr", "<", $item->right_ptr);
    }

    if (isset($p->name)) {
      $orm->where("name", "LIKE", "%" . Database::escape_for_like($p->name) . "%");
    }

    if (isset($p->type)) {
      $orm->where("type", "IN", explode(",", $p->type));
    }

    // Apply the item's sort order, using id as the tie breaker.
    // See Item_Model::children()
    $order_by = array($item->sort_column => $item->sort_order);
    if ($item->sort_column != "id") {
      $order_by["id"] = "ASC";
    }
    $orm->order_by($order_by);

    $result = array(
      "url" => $request->url,
      "entity" => $item->as_restful_array(),
      "relationships" => rest::relationships("item", $item));
    if ($item->is_album()) {
      $result["members"] = array();
      foreach ($orm->find_all() as $child) {
        $result["members"][] = rest::url("item", $child);
      }
    }

    return $result;
  }

  static function put($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    if ($entity = $request->params->entity) {
      // Only change fields from a whitelist.
      foreach (array("album_cover", "captured", "description",
                     "height", "mime_type", "name", "parent", "rand_key", "resize_dirty",
                     "resize_height", "resize_width", "slug", "sort_column", "sort_order",
                     "thumb_dirty", "thumb_height", "thumb_width", "title", "view_count",
                     "width") as $key) {
        switch ($key) {
        case "album_cover":
          if (property_exists($entity, "album_cover")) {
            $album_cover_item = rest::resolve($entity->album_cover);
            access::required("view", $album_cover_item);
            $item->album_cover_item_id = $album_cover_item->id;
          }
          break;

        case "parent":
          if (property_exists($entity, "parent")) {
            $parent = rest::resolve($entity->parent);
            access::required("edit", $parent);
            $item->parent_id = $parent->id;
          }
          break;
        default:
          if (property_exists($entity, $key)) {
            $item->$key = $entity->$key;
          }
        }
      }
    }

    // Replace the data file, if required
    if (($item->is_photo() || $item->is_movie()) && isset($request->file)) {
      $item->set_data_file($request->file);
    }

    $item->save();

    if (isset($request->params->members) && $item->sort_column == "weight") {
      $weight = 0;
      foreach ($request->params->members as $url) {
        $child = rest::resolve($url);
        if ($child->parent_id == $item->id && $child->weight != $weight) {
          $child->weight = $weight;
          $child->save();
        }
        $weight++;
      }
    }
  }

  static function post($request) {
    $parent = rest::resolve($request->url);
    access::required("add", $parent);

    $entity = $request->params->entity;
    $item = ORM::factory("item");
    switch ($entity->type) {
    case "album":
      $item->type = "album";
      $item->parent_id = $parent->id;
      $item->name = $entity->name;
      $item->title = isset($entity->title) ? $entity->title : $entity->name;
      $item->description = isset($entity->description) ? $entity->description : null;
      $item->slug = isset($entity->slug) ? $entity->slug : null;
      $item->save();
      break;

    case "photo":
    case "movie":
      if (empty($request->file)) {
        throw new Rest_Exception(
          "Bad Request", 400, array("errors" => array("file" => t("Upload failed"))));
      }
    $item->type = $entity->type;
    $item->parent_id = $parent->id;
    $item->set_data_file($request->file);
    $item->name = $entity->name;
    $item->title = isset($entity->title) ? $entity->title : $entity->name;
    $item->description = isset($entity->description) ? $entity->description : null;
    $item->slug = isset($entity->slug) ? $entity->slug : null;
    $item->save();
    break;

    default:
      throw new Rest_Exception(
        "Bad Request", 400, array("errors" => array("type" => "invalid")));
    }

    return array("url" => rest::url("item", $item));
  }

  static function delete($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    $item->delete();
  }

  static function resolve($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }
    return $item;
  }

  static function url($item) {
    return url::abs_site("rest/item/{$item->id}");
  }
}
