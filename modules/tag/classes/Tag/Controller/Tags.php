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
  /**
   * Show a tag cloud.
   */
  public function action_index() {
    // Far from perfection, but at least require view permission for the root album
    $album = ORM::factory("Item", 1);
    Access::required("view", $album);

    $this->response->body(Tag::cloud(Module::get_var("tag", "tag_cloud_size", 30)));
  }

  /**
   * Add a tag to an item.  This generates the form, validates it, adds the tag, and returns a
   * response.  This can be used as an ajax form (preferable) or a normal view.
   */
  public function action_add() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    // Build our form.
    $form = Formo::form()
      ->attr("id", "g-add-tag-form")
      ->add_class("g-short-form")
      ->add("tag", "group");
    $form->tag
      ->set("label", t("Add Tag"))
      ->add("name", "input")
      ->add("item_id", "input|hidden", $item->id)
      ->add("submit", "input|submit", t("Add Tag"));
    $form->tag->name
      ->set("label", Arr::get(array(
          "album" => t("Add tag to album"),
          "photo" => t("Add tag to photo"),
          "movie" => t("Add tag to movie")
        ), $item->type))
      ->add_rule("not_empty")
      ->add_rule("max_length", array(":value", 64), t("Your tag is too long"));

    // If sent, validate and create the tag.
    if ($form->load()->validate()) {
      foreach (explode(",", $form->tag->name->val()) as $tag_name) {
        $tag_name = trim($tag_name);
        if ($tag_name) {
          $tag = Tag::add($item, $tag_name);
        }
      }

      $form->set("response", array(
        "cloud" => (string)Tag::cloud(Module::get_var("tag", "tag_cloud_size", 30))));
    }

    $this->response->ajax_form($form);
  }

  /**
   * Return a list of tag names for autocomplete.
   */
  public function action_autocomplete() {
    $tags = array();
    $tag_parts = explode(",", $this->request->query("term"));
    $tag_part = ltrim(end($tag_parts));
    $tag_list = ORM::factory("Tag")
      ->where("name", "LIKE", Database::escape_for_like($tag_part) . "%")
      ->order_by("name", "ASC")
      ->limit(100)
      ->find_all();
    foreach ($tag_list as $tag) {
      $tags[] = (string)HTML::clean($tag->name);
    }

    $this->response->ajax(json_encode($tags));
  }
}
