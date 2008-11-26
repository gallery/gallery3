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
  public static $NUMBER_OF_BUCKETS = 7;
  
  /**
   * Associate a tag with an item.  Create the tag if it doesn't already exist.
   *
   * @todo Write test.
   *
   * @param ORM    $item an item
   * @param string $tag_name a tag name
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
  }

  /**
   * Assign a css class to the tags based on frequency of use.  This code is based on the code
   * from: http://www.hawkee.com/snippet/1485/
   *
   * @return array List of tags each entry has the following format:
   *                array("id" => "tag_id", "name" => "tag_name", "count" => "frequency", 
   *                      "class" => "bucket") 
   */
  public static function load_buckets() {
    $tag_list = array();
    $tags = ORM::factory("tag")
      ->orderby("count", "DESC")
      ->limit(30)
      ->find_all();
    if ($tags->count() > 0) {
      $max_count = $tags[0]->count;
      foreach($tags as $key => $tag) {
        //  Set the tag to the current class
        $size = (int)(($tag->count / $max_count) * (self::$NUMBER_OF_BUCKETS - 1));
        $tag_list[$key] = array("id" => $tag->id, "name" => $tag->name, "count" => $tag->count, 
          "class" => "$size");
      }
      usort($tag_list, array("tag", "alphasort"));
    }
    return $tag_list;
  }

  public static function alphasort($tag1, $tag2) {
     if ($tag1["name"] == $tag2["name"]) {
       return 0;
     }
     return $tag1["name"] < $tag2["name"] ? -1 : 1;
  }
}
