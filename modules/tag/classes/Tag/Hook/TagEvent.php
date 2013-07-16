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
class Tag_Hook_TagEvent {
  /**
   * Initialization.  This sets the tag routes.
   */
  static function gallery_ready() {
    Route::set("tag", "tag(/<tag_url>)", array("tag_url" => "[A-Za-z0-9-_/,]++"))
      ->defaults(array(
          "controller" => "tags",
          "action" => "show"
        ));

    // This route is for Gallery 3.0.x tag_name URLs, and fires a 301 redirect to the canonical URL.
    Route::set("tag_name", "tag_name/<args>")
      ->defaults(array(
          "controller" => "tags",
          "action" => "find_by_name"
        ));
  }

  /**
   * Setup the relationship between Model_Item and Model_Tag.
   */
  static function model_relationships($relationships) {
    $relationships["item"]["has_many"]["tags"] =
      array("through" => "items_tags", "delete_through" => true);
    $relationships["tag"]["has_many"]["items"] =
      array("through" => "items_tags", "delete_through" => true, "track_changed_through" => true);
  }

  /**
   * Add tags from an image file's IPTC ("Keywords" field).
   */
  static function item_created($item) {
    Tag::add_from_metadata($item);
  }

  /**
   * Add tags from an image file's IPTC ("Keywords" field).
   */
  static function item_updated_data_file($item) {
    Tag::add_from_metadata($item);
  }

  static function item_deleted($item) {
    Tag::clear_all($item);
    if (!Batch::in_progress()) {
      Tag::compact();
    }
  }

  static function batch_complete() {
    Tag::compact();
  }

  static function item_edit_form($item, $form) {
    $tag_names = array();
    foreach ($item->tags->find_all() as $tag) {
      $tag_names[] = $tag->name;
    }
    $form->add_before_submit("tags", "input", implode(", ", $tag_names));
    $form->find("tags")->set("label", t("Tags (comma separated)"));
    $form->add_script_text(static::_get_autocomplete_js());
  }

  static function item_edit_form_completed($item, $form) {
    Tag::clear_all($item);
    foreach (explode(",", $form->find("tags")->val()) as $tag_name) {
      $tag_name = trim($tag_name);
      if (!empty($tag_name)) {
        Tag::add($item, $tag_name);
      }
    }
    Tag::compact();
  }

  static function item_add_form($parent, $form) {
    $form->add_before_submit("tags", "input");
    $form->find("tags")->set("label", t("Add tags to all uploaded files"));
    $form->add_script_text(static::_get_autocomplete_js());
  }

  static function item_add_form_completed($item, $form) {
    foreach (explode(",", $form->find("tags")->val()) as $tag_name) {
      $tag_name = trim($tag_name);
      if (!empty($tag_name)) {
        $tag = Tag::add($item, $tag_name);
      }
    }
  }

  static function admin_menu($menu, $theme) {
    $menu->get("content_menu")
      ->append(Menu::factory("link")
               ->id("tags")
               ->label(t("Tags"))
               ->url(URL::site("admin/tags")));
  }

  static function item_index_data($item, $data) {
    foreach ($item->tags->find_all() as $tag) {
      $data[] = $tag->name;
    }
  }

  static function info_block_get_metadata($block, $item) {
    $tags = array();
    foreach ($item->tags->find_all() as $tag) {
      $tags[] = "<a href=\"{$tag->url()}\">" .
        HTML::clean($tag->name) . "</a>";
    }
    if ($tags) {
      $info = $block->content->metadata;
      $info["tags"] = array(
        "label" => t("Tags:"),
        "value" => implode(", ", $tags)
      );
      $block->content->metadata = $info;
    }
  }

  protected static function _get_autocomplete_js() {
    $url = URL::site("tags/autocomplete");
    return "$('input[name=\"tags\"]').gallery_autocomplete('$url', {multiple: true});";
  }
}
