<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class gallery_rest_Core {
  static function get($request) {
    $path = implode("/", $request->arguments);

    $item = gallery_rest::_get_item($path);

    $parent = $item->parent();
    $response_data = array("type" => $item->type,
                           "name" => $item->name,
                           "path" => $item->relative_url(),
                           "parent_path" => empty($parent) ? null : $parent->relative_url(),
                           "title" => $item->title,
                           "thumb_url" => $item->thumb_url(true),
                           "thumb_size" => array("height" => $item->thumb_height,
                                                 "width" => $item->thumb_width),
                           "resize_url" => $item->resize_url(true),
                           "resize_size" => array("height" => (int)$item->resize_height,
                                                  "width" => (int)$item->resize_width),
                           "url" => $item->file_url(true),
                           "size" => array("height" => $item->height,
                                           "width" => $item->width),
                           "description" => $item->description,
                           "slug" => $item->slug);

    $children = self::_get_children($item, $request);
    if (!empty($children) || $item->is_album()) {
      $response_data["children"] = $children;
    }
    return rest::success(array("resource" => $response_data));
  }

  static function put($request) {
    if (empty($request->arguments)) {
      Rest_Exception::trigger(400, "Bad request");
    }
    $path = implode("/", $request->arguments);
    $item = gallery_rest::_get_item($path, "edit");

    // Validate the request data
    $new_values = gallery_rest::_validate($request, $item->parent_id, $item->id);
    $errors = $new_values->errors();
    if (empty($errors)) {
      $item->title = $new_values->title;
      $item->description = $new_values->description;
      if ($item->id != 1) {
        $item->rename($new_values->name);
      }
      $item->slug = $new_values->slug;
      $item->save();

      log::success("content", "Updated $item->type",
                   "<a href=\"{$item->type}s/$item->id\">view</a>");

      return rest::success();
    } else {
      return rest::validation_error($errors);
    }
  }

  static function post($request) {
    if (empty($request->arguments)) {
      Rest_Exception::trigger(400, "Bad request");
    }

    $components = $request->arguments;
    $name = urldecode(array_pop($components));

    $parent = gallery_rest::_get_item(implode("/", $components), "edit");

    // Validate the request data
    $request->name = $name;
    $new_values = gallery_rest::_validate($request, $parent->id);
    $errors = $new_values->errors();
    if (!empty($errors)) {
      return rest::validation_error($errors);
    }

    if (empty($new_values["image"])) {
      $new_item = album::create(
        $parent,
        $name,
        empty($new_values["title"]) ? $name : $new_values["title"],
        empty($new_values["description"]) ? null : $new_values["description"],
        identity::active_user()->id,
        empty($new_values["slug"]) ? $name : $new_values["slug"]);
      $log_message = t("Added an album");
    } else {
      $temp_filename = upload::save("image");
      $path_info = @pathinfo($temp_filename);
      if (array_key_exists("extension", $path_info) &&
          in_array(strtolower($path_info["extension"]), array("flv", "mp4"))) {
        $new_item =
          movie::create($parent, $temp_filename, $new_values["name"], $new_values["title"]);
        $log_message = t("Added a movie");
      } else {
        $new_item =
          photo::create($parent, $temp_filename, $new_values["name"], $new_values["title"]);
        $log_message = t("Added a photo");
      }
    }

    log::success("content", $log_message, "<a href=\"{$new_item->type}s/$new_item->id\">view</a>");

    return rest::success(array("path" => $new_item->relative_url()));
  }

  static function delete($request) {
    if (empty($request->arguments)) {
      Rest_Exception::trigger(400, "Bad request", $log_message);
      return rest::invalid_request();
    }
    $path = implode("/", $request->arguments);

    $item = gallery_rest::_get_item($path, "edit");

    if ($item->id == 1) {
      Rest_Exception::trigger(400, "Bad request", "Attempt to delete the root album");
    }

    $parent = $item->parent();
    $item->delete();

    if ($item->is_album()) {
      $msg = t("Deleted album <b>%title</b>", array("title" => html::purify($item->title)));
    } else {
      $msg = t("Deleted photo <b>%title</b>", array("title" => html::purify($item->title)));
    }
    log::success("content", $msg);

    return rest::success(array("resource" => array("parent_path" => $parent->relative_url())));
  }

  private static function _get_item($path, $permission="view") {
    $item = url::get_item_from_uri($path);

    if (!$item->loaded()) {
      throw new Kohana_404_Exception();
    }

    if (!access::can($permission, $item)) {
      throw new Kohana_404_Exception();
    }

    return $item;
  }

  private static function _get_children($item, $request) {
    $children = array();
    $limit = empty($request->limit) ? null : $request->limit;
    $offset = empty($request->offset) ? null : $request->offset;
    $where = empty($request->filter) ? array() : array("type" => $request->filter);
    foreach ($item->viewable()->children($limit, $offset, $where) as $child) {
      $children[] = array("type" => $child->type,
                          "has_children" => $child->children_count() > 0,
                          "path" => $child->relative_url(),
                          "thumb_url" => $child->thumb_url(true),
                          "thumb_dimensions" => array("width" => $child->thumb_width,
                                                      "height" => $child->thumb_height),
                          "has_thumb" => $child->has_thumb(),
                          "title" => $child->title);
    }

    return $children;
  }

  private static function _validate($request, $parent_id, $item_id=0) {
    $item = ORM::factory("item", $item_id);

    // Normalize the inputs so all fields have a value
    $new_values = Validation::factory(array());
    foreach ($item->form_rules as $field => $rule_set) {
      if (isset($request->$field)) {
        $new_values[$field] = $request->$field;
      } else if (isset($item->$field)) {
        $new_values[$field] = $item->$field;
      }
      foreach (explode("|", $rule_set) as $rule) {
        $new_values->add_rules($field, $rule);
      }
    }
    $name = $new_values["name"];
    $new_values["title"] = empty($new_values["title"]) ? $name : $new_values["title"];
    $new_values["description"] =
      empty($new_values["description"]) ? null : $new_values["description"];
    $new_values["slug"] = empty($new_values["slug"]) ? $name : $new_values["slug"];

    if (!empty($request->image)) {
      $new_values["image"] = $request->image;
      $new_values->add_rules(
        "image", "upload::valid", "upload::required", "upload::type[gif,jpg,jpeg,png,flv,mp4]");
    }

    if ($new_values->validate() && $item_id != 1) {
      $errors = gallery_rest::_check_for_conflicts($parent_id, $item_id,
                                                   $new_values["name"], $new_values["slug"]);
      if (!empty($errors)) {
        !empty($errors["name_conflict"]) OR $new_values->add_error("name", "Duplicate name");
        !empty($errors["slug_conflict"]) OR
          $new_values->add_error("slug", "Duplicate Internet address");
      }
    }

    return $new_values;
  }

  private static function _check_for_conflicts($parent_id, $item_id, $new_name, $new_slug) {
    $errors = array();

    if ($row = db::build()
        ->select(array("name", "slug"))
        ->from("items")
        ->where("parent_id", "=", $parent_id)
        ->where("id", "<>", $item_id)
        ->and_open()
        ->where("name", "=", $new_name)
        ->or_where("slug", "=", $new_slug)
        ->close()
        ->execute()
        ->current()) {
      if ($row->name == $new_name) {
        $errors["name_conflict"] = 1;
      }
      if ($row->slug == $new_slug) {
        $errors["slug_conflict"] = 1;
      }
    }

    return $errors;
  }
}
