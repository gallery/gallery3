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
            foreach (preg_split("/,/", $tag) as $word) {
              $word = trim($word);
              if (function_exists("mb_detect_encoding") && mb_detect_encoding($word) != "UTF-8") {
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
        Kohana::log("error", "Error adding tag: $tag\n" .
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
      ->text("$('form input[id=tags]').ready(function() {
                $('form input[id=tags]').autocomplete(
                  '$url', {max: 30, multiple: true, multipleSeparator: ',', cacheLength: 1});
              });");
    $tag_value = implode(", ", tag::item_tags($item));
    $form->edit_item->input("tags")->label(t("Tags (comma separated)"))
      ->value($tag_value);
  }

  static function item_edit_form_completed($item, $form) {
    tag::clear_all($item);
    foreach (preg_split("/,/", $form->edit_item->tags->value) as $tag_name) {
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
    $data[] = join(" ", tag::item_tags($item));
  }
}
