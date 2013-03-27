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
   * Handle the creation of a new photo.
   * @todo Get tags from the XMP and/or IPTC data in the image
   *
   * @param Model_Item $photo
   */
  static function item_created($photo) {
    $tags = array();
    if ($photo->is_photo()) {
      $path = $photo->file_path();
      $size = getimagesize($photo->file_path(), $info);
      if (is_array($info) && !empty($info["APP13"])) {
        $iptc = iptcparse($info["APP13"]);
        if (!empty($iptc["2#025"])) {
          foreach($iptc["2#025"] as $tag) {
            $tag = str_replace("\0",  "", $tag);
            foreach (explode(",", $tag) as $word) {
              $word = trim($word);
              $word = Encoding::convert_to_utf8($word);
              $tags[$word] = 1;
            }
          }
        }
      }
    }

    // @todo figure out how to read the keywords from xmp
    foreach(array_keys($tags) as $tag) {
      try {
        Tag::add($photo, $tag);
      } catch (Exception $e) {
        Log::add("error", "Error adding tag: $tag\n" .
                    $e->getMessage() . "\n" . $e->getTraceAsString());
      }
    }

    return;
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
    foreach (Tag::item_tags($item) as $tag) {
      $tag_names[] = $tag->name;
    }
    $form->edit_item->input("tags")->label(t("Tags (comma separated)"))
      ->value(implode(", ", $tag_names));

    $form->script("")->text(self::_get_autocomplete_js());
  }

  static function item_edit_form_completed($item, $form) {
    Tag::clear_all($item);
    foreach (explode(",", $form->edit_item->tags->value) as $tag_name) {
      if ($tag_name) {
        Tag::add($item, trim($tag_name));
      }
    }
    Module::event("item_related_update", $item);
    Tag::compact();
  }

  static function admin_menu($menu, $theme) {
    $menu->get("content_menu")
      ->append(Menu::factory("link")
               ->id("tags")
               ->label(t("Tags"))
               ->url(URL::site("admin/tags")));
  }

  static function item_index_data($item, $data) {
    foreach (Tag::item_tags($item) as $tag) {
      $data[] = $tag->name;
    }
  }

  static function add_photos_form($album, $form) {
    $group = $form->add_photos;

    $group->input("tags")
      ->label(t("Add tags to all uploaded files"))
      ->value("");

    $group->script("")->text(self::_get_autocomplete_js());
  }

  static function add_photos_form_completed($item, $form) {
    $group = $form->add_photos;

    foreach (explode(",", $group->tags->value) as $tag_name) {
      $tag_name = trim($tag_name);
      if ($tag_name) {
        $tag = Tag::add($item, $tag_name);
      }
    }
  }

  static function info_block_get_metadata($block, $item) {
    $tags = array();
    foreach (Tag::item_tags($item) as $tag) {
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

  private static function _get_autocomplete_js() {
    $url = URL::site("tags/autocomplete");
    return "$('input[name=\"tags\"]').gallery_autocomplete('$url', {multiple: true});";
  }
}
