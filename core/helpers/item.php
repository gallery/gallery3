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
class item_Core {
  static function move($source, $target) {
    access::required("edit", $source);
    access::required("edit", $target);

    $parent = $source->parent();
    if ($parent->album_cover_item_id == $source->id) {
      if ($parent->children_count() > 1) {
        foreach ($parent->children(2) as $child) {
          if ($child->id != $source->id) {
            $new_cover_item = $child;
            break;
          }
        }
        item::make_album_cover($new_cover_item);
      } else {
        item::remove_album_cover($parent);
      }
    }

    $source->move_to($target);

    // If the target has no cover item, make this it.
    if ($target->album_cover_item_id == null)  {
      item::make_album_cover($source);
    }
  }

  static function make_album_cover($item) {
    $parent = $item->parent();
    access::required("edit", $parent);

    model_cache::clear("item", $parent->album_cover_item_id);
    $parent->album_cover_item_id = $item->is_album() ? $item->album_cover_item_id : $item->id;
    $parent->thumb_dirty = 1;
    $parent->save();
    graphics::generate($parent);
    $grand_parent = $parent->parent();
    if ($grand_parent && $grand_parent->album_cover_item_id == null)  {
      item::make_album_cover($parent);
    }
  }

  static function remove_album_cover($album) {
    access::required("edit", $album);
    @unlink($album->thumb_path());

    model_cache::clear("item", $album->album_cover_item_id)  ;
    $album->album_cover_item_id = null;
    $album->thumb_width = 0;
    $album->thumb_height = 0;
    $album->thumb_dirty = 1;
    $album->save();
    graphics::generate($album);
  }

  static function validate_no_slashes($input) {
    if (strpos($input->value, "/") !== false) {
      $input->add_error("no_slashes", 1);
    }
  }

  static function validate_no_trailing_period($input) {
    if (rtrim($input->value, ".") !== $input->value) {
      $input->add_error("no_trailing_period", 1);
    }
  }

  static function validate_no_name_conflict($input) {
    $itemid = Input::instance()->post("item");
    if (is_array($itemid)) {
      $itemid = $itemid[0];
    }
    $item = ORM::factory("item")
      ->in("id", $itemid)
      ->find();
    if (Database::instance()
        ->from("items")
        ->where("parent_id", $item->parent_id)
        ->where("id <>", $item->id)
        ->where("name", $input->value)
        ->count_records()) {
      $input->add_error("conflict", 1);
    }
  }
}