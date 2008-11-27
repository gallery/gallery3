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
   * Associate a tag with an item.  Create the tag if it doesn't already exist.
   *
   * @todo Write test.
   *
   * @param Item_Model $item an item
   * @param string     $tag_name a tag name
   * @return Tag_Model
   * @throws Exception("@todo {$tag_name} WAS_NOT_ADDED_TO {$item->id}")
   */
  public static function add($item, $tag_name) {
    $tag = ORM::factory("tag")->where("name", $tag_name)->find();
    if (!$tag->loaded) {
      $tag->name = $tag_name;
      $tag->count = 0;
      // Need to save it now to get an id assigned.
      $tag->save();
    }

    if (!$tag->has($item)) {
      // Note: ORM::has() causes a database lookup.  ORM::add() calls ORM::has() a second time,
      // so we're doing an extra database lookup just to make sure that we don't increment the
      // count value if the tag already existed.
      if (!$tag->add($item, $tag)) {
        throw new Exception("@todo {$tag->name} WAS_NOT_ADDED_TO {$item->id}");
      }
      $tag->count++;
      $tag->save();
    }
    return $tag;
  }

  /**
   * Return the N most popular tags.
   *
   * @return ORM_Iterator of Tag_Model in descending tag count order
   */
  public static function popular_tags($count) {
    return ORM::factory("tag")
      ->orderby("count", "DESC")
      ->limit($count)
      ->find_all();
  }
}
