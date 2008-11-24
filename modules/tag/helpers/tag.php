<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

class tag_Core {

  /**
   * Adds the specified tags to the item
   *
   * @todo Write test.
   *
   * @param mixed $items a single item or an array of items
   * @param mixed $tag_names a single tag name or an array of tag names
   * @throws Exception("@todo {tag_name} WAS_NOT_ADDED_TO {$item_id}")
   */
  public static function add_tag($items, $tag_names) {
    $items = is_array($items) ? $items : array($items);
    $tag_names = is_array($tag_names) ? $tag_names : array($tag_names);

    foreach($items as $item) {
      foreach ($tag_names as $tag_name) {
        $tag = ORM::factory("tag")->where("name", $tag_name)->find();
        if (!$tag->loaded) {
          $tag->name = $tag_name;
          $tag->count = 0;
          // Need to save it now to get an id assigned.
          $tag->save();
        }

        $tag_has_item = false;
        foreach($tag->items as $tagged_item) {
          if ($tagged_item->id == $item->id) {
            $tag_has_item = true;
            break;
          }
        }
        if (!$tag_has_item) {
          $tag->count++;
          $tag->save();
          if (!$tag->add($item, $tag)) {
            throw new Exception("@todo {$tag->name} WAS_NOT_ADDED_TO {$item->id}");
          }
        }
      }
    }
  }
}
