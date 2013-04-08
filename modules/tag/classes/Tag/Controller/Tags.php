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
class Tag_Controller_Tags extends Controller {
  public function action_index() {
    // Far from perfection, but at least require view permission for the root album
    $album = ORM::factory("Item", 1);
    Access::required("view", $album);

    print Tag::cloud(Module::get_var("tag", "tag_cloud_size", 30));
  }

  public function action_create($item_id) {
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    $form = Tag::get_add_form($item);
    if ($form->validate()) {
      foreach (explode(",", $form->add_tag->inputs["name"]->value) as $tag_name) {
        $tag_name = trim($tag_name);
        if ($tag_name) {
          $tag = Tag::add($item, $tag_name);
        }
      }

      JSON::reply(array("result" => "success", "cloud" => (string)Tag::cloud(30)));
    } else {
      JSON::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function action_autocomplete() {
    $tags = array();
    $tag_parts = explode(",", Request::$current->query("term"));
    $tag_part = ltrim(end($tag_parts));
    $tag_list = ORM::factory("Tag")
      ->where("name", "LIKE", Database::escape_for_like($tag_part) . "%")
      ->order_by("name", "ASC")
      ->limit(100)
      ->find_all();
    foreach ($tag_list as $tag) {
      $tags[] = (string)HTML::clean($tag->name);
    }

    Ajax::response(json_encode($tags));
  }
}
