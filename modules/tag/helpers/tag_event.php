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
class tag_event_Core {
  /**
   * Handle the creation of a new photo.
   * @todo Get tags from the XMP and/or IPTC data in the image
   *
   * @param Item_Model $photo
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
              if (function_exists("mb_detect_encoding") &&
                  mb_detect_encoding($word, "ISO-8859-1, UTF-8") != "UTF-8") {
                $word = utf8_encode($word);
              }
              $tags[$word] = 1;
            }
          }
        }
      }
    }

    // @todo figure out how to read the keywords from xmp
    foreach(array_keys($tags) as $tag) {
      try {
        tag::add($photo, $tag);
      } catch (Exception $e) {
        Kohana_Log::add("error", "Error adding tag: $tag\n" .
                    $e->getMessage() . "\n" . $e->getTraceAsString());
      }
    }

    return;
  }

  static function item_deleted($item) {
    tag::clear_all($item);
    tag::compact();
  }

  static function item_edit_form($item, $form) {
    $url = url::site("tags/autocomplete");
    $form->script("")
      ->text("$('form input[name=tags]').ready(function() {
                $('form input[name=tags]').autocomplete(
                  '$url', {max: 30, multiple: true, multipleSeparator: ',', cacheLength: 1});
              });");

    $tag_names = array();
    foreach (tag::item_tags($item) as $tag) {
      $tag_names[] = $tag->name;
    }
    $form->edit_item->input("tags")->label(t("Tags (comma separated)"))
      ->value(implode(", ", $tag_names));
  }

  static function item_edit_form_completed($item, $form) {
    tag::clear_all($item);
    foreach (explode(",", $form->edit_item->tags->value) as $tag_name) {
      if ($tag_name) {
        tag::add($item, trim($tag_name));
      }
    }
    tag::compact();
  }

  static function admin_menu($menu, $theme) {
    $menu->get("content_menu")
      ->append(Menu::factory("link")
               ->id("tags")
               ->label(t("Tags"))
               ->url(url::site("admin/tags")));
  }

  static function item_index_data($item, $data) {
    foreach (tag::item_tags($item) as $tag) {
      $data[] = $tag->name;
    }
  }

  static function add_photos_form($album, $form) {
    if (!isset($group->uploadify)) {
      return;
    }
    
    $group = $form->add_photos;
    $group->input("tags")
      ->label(t("Add tags to all uploaded files"))
      ->value("");
    $group->uploadify->script_data("tags", "");

    $autocomplete_url = url::site("tags/autocomplete");
    $group->script("")
      ->text("$('input[name=tags]')
                .autocomplete(
                  '$autocomplete_url',
                  {max: 30, multiple: true, multipleSeparator: ',', cacheLength: 1}
                )
                .change(function (event) {
                  $('#g-uploadify').uploadifySettings('scriptData', {'tags': $(this).val()});
                });");
  }

  static function add_photos_form_completed($album, $form) {
    if (!isset($group->uploadify)) {
      return;
    }
    
    foreach (explode(",", $form->add_photos->tags->value) as $tag_name) {
      $tag_name = trim($tag_name);
      if ($tag_name) {
        $tag = tag::add($album, $tag_name);
      }
    }
  }
}
