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
    if (empty($request->path)) {
      $request->path = "";
    }

    $item = ORM::factory("item")
      ->where("relative_url_cache", $request->path)
      ->viewable()
      ->find();

    if (!$item->loaded) {
      return rest::not_found("Resource: {$request->path} missing.");
    }

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
    $item = ORM::factory("item")
      ->where("relative_url_cache", $request->path)
      ->viewable()
      ->find();

    if (!$item->loaded) {
      return rest::not_found("Resource: {$request->path} missing.");
    }

    if (!access::can("edit", $item)) {
      return rest::not_found("Resource: {$request->path} permission denied.");
    }

    // Validate the request data
    $new_values = gallery_rest::_validate($item, $request);
    $errors = $new_values->errors();
    if (empty($errors)) {
      item::update($item, $new_values->as_array());

      log::success("content", "Updated $item->type", "<a href=\"{$item->type}s/$item->id\">view</a>");

      return rest::success();
    } else {
      return rest::validation_error($errors);
    }
  }

  static function post($request) {
    if (empty($request->path)) {
      return rest::invalid_request();
    }

    $components = explode("/", $request->path);
    $name = urldecode(array_pop($components));

    $parent = ORM::factory("item")
      ->where("relative_url_cache", implode("/", $components))
      ->viewable()
      ->find();

    if (!$parent->loaded) {
      return rest::not_found("Resource: {$request->path} missing.");
    }

    if (!access::can("edit", $parent)) {
      return rest::not_found("Resource: {$request->path} permission denied.");
    }

    // @TODO validate input values (assume nothing about the quality of input)

    if (empty($_FILES["image"])) {
      $new_item = album::create(
        $parent,
        $name,
        empty($request->title) ? $name : $request->title,
        empty($request->description) ? null : $request->description,
        identity::active_user()->id,
        empty($request->slug) ? $name : $request->slug);
      $log_message = t("Added an album");
    } else {
      $file_validation = new Validation($_FILES);
      $file_validation->add_rules(
        "image", "upload::valid", "upload::required", "upload::type[gif,jpg,jpeg,png,flv,mp4]");
      if (!$file_validation->validate()) {
        $errors = $file_validation->errors();
        return rest::fail(
          $errors["image"] == "type" ? "Upload failed: Unsupported file type" :
                                       "Upload failed: Uploaded file missing");
      }
      $temp_filename = upload::save("image");
      $name = substr(basename($temp_filename), 10);  // Skip unique identifier Kohana adds
      $title = item::convert_filename_to_title($name);
      $path_info = @pathinfo($temp_filename);
      if (array_key_exists("extension", $path_info) &&
          in_array(strtolower($path_info["extension"]), array("flv", "mp4"))) {
        $new_item = movie::create($parent, $temp_filename, $name, $title);
        $log_message = t("Added a movie");
      } else {
        $new_item = photo::create($parent, $temp_filename, $name, $title);
        $log_message = t("Added a photo");
      }
    }

    log::success("content", $log_message, "<a href=\"{$new_item->type}s/$new_item->id\">view</a>");

    return rest::success(array("path" => $new_item->relative_url()));
  }

  static function delete($request) {
    if (empty($request->path)) {
      return rest::invalid_request();
    }

    $item = ORM::factory("item")
      ->where("relative_url_cache", $request->path)
      ->viewable()
      ->find();

    if (!$item->loaded) {
      return rest::success();
    }

    if (!access::can("edit", $item)) {
      return rest::not_found("Resource: {$request->path} permission denied.");
    }

    if ($item->id == 1) {
      return rest::invalid_request("Attempt to delete the root album");
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

  private static function _validate($item, $request) {
    $new_values = array();
    $fields = array("title", "description", "name", "slug");
    if ($item->id == 1) {
      unset($request["name"]);
      unset($request["slug"]);
    }
    foreach ($fields as $field) {
      $new_values[$field] = isset($request->$field) ? $request->$field : $item->$field;
    }

    $new_values = new Validation($new_values);
    foreach ($item->rules as $field => $rules) {
      foreach (explode("|", $rules) as $rule) {
        $new_values->add_rules($field, $rule);
      }
    }

    if (($valid = $new_values->validate()) && $item->id != 1) {
      $errors = item::check_for_conflicts($item, $new_values["name"], $new_values["slug"]);
      if ($valid = empty($errors)) {
        !empty($errors["name_conflict"]) OR $new_values->add_error("name", "Duplicate Name");
        !empty($errors["slug_conflict"]) OR
          $new_values->add_error("name", "Duplicate Internet Address");
      }
    }

    return $new_values;
  }
}
