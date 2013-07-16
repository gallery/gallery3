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
class Tag_Tag {
  /**
   * Associate a tag with an item.  Create the tag if it doesn't already exist.
   *
   * @param Model_Item $item an item
   * @param string     $tag_name a tag name
   * @return Model_Tag
   * @throws Gallery_Exception("missing tag name")
   */
  static function add($item, $tag_name) {
    if (empty($tag_name)) {
      throw new Gallery_Exception("missing tag name");
    }

    $tag = ORM::factory("Tag")->where("name", "=", $tag_name)->find();
    if (!$tag->loaded()) {
      $tag->name = $tag_name;
      $tag->save();
    }

    if (!$tag->has("items", $item)) {
      $tag->add("items", $item);
      $tag->save();
    }

    return $tag;
  }

  /**
   * Return the N most popular tags.
   *
   * @return Database_Result of Model_Tag in descending tag count order
   */
  static function popular_tags($count) {
    $count = max($count, 1);
    return ORM::factory("Tag")
      ->limit($count)
      ->find_all();
  }

  /**
   * Return a rendering of the cloud for the N most popular tags.
   *
   * @param integer $count the number of tags
   * @return View
   */
  static function cloud($count) {
    $tags = Tag::popular_tags($count)->as_array();
    if ($tags) {
      $cloud = new View("tag/cloud.html");
      $cloud->max_count = $tags[0]->count;
      if (!$cloud->max_count) {
        return;
      }
      usort($tags, array("tag", "sort_by_name"));
      $cloud->tags = $tags;
      return $cloud;
    }
  }

  static function sort_by_name($tag1, $tag2) {
    return strcasecmp($tag1->name, $tag2->name);
  }

  /**
   * Delete all tags associated with an item.
   */
  static function clear_all($item) {
    // Re-save each tag so their counts are updated.
    foreach ($item->tags->find_all() as $tag) {
      $tag->save();
    }
    // Remove the tags.  This doesn't actually affect the item model, so we don't need to save it.
    $item->remove("tags");
    // Since we don't save the tag model, we need to run the "item_related_update" event ourselves.
    Module::event("item_related_update", $item);
  }

  /**
   * Remove all items from a tag.
   */
  static function remove_items($tag) {
    $tag->remove("items");
    $tag->save();
  }

  /**
   * Get rid of any tags that have no associated items.
   */
  static function compact() {
    // @todo There's a potential race condition here which we can solve by adding a lock around
    // this and all the cases where we create/update tags.  I'm loathe to do that since it's an
    // extremely rare case.
    DB::delete("tags")->where("count", "=", 0)->execute();
  }

  /**
   * Add tags from an image file's IPTC ("Keywords" field).
   * @todo  consider adding XMP data in here, too.
   */
  static function add_from_metadata($item) {
    if ($item->is_photo()) {
      $iptc = Photo::get_file_iptc($item->file_path());
      if (!empty($iptc["Keywords"])) {
        // Implode array of CSV to one CSV string, explode into individual values, trim
        // each, then filter out empty ones (which would otherwise throw exceptions).
        $tags = array_filter(array_map("trim", explode(",", implode(",", $iptc["Keywords"]))));
        foreach($tags as $tag) {
          Tag::add($item, $tag);
        }
      }
    }
  }

  /**
   * Return the absolute url for an array of tags.
   * @param  array   array of Model_Tag objects
   * @param  string  (optional) query string (e.g. "page=3")
   */
  static function abs_url($tags, $query=null) {
    $url = array_shift($tags)->abs_url();
    foreach ($tags as $tag) {
      $url .= ",{$tag->slug}";
    }

    return $url . ($query ? "?$query" : "");
  }

  /**
   * Return the url for an array of tags.
   * @param  array   array of Model_Tag objects
   * @param  string  (optional) query string (e.g. "page=3")
   */
  static function url($tags, $query=null) {
    $url = array_shift($tags)->url();
    foreach ($tags as $tag) {
      $url .= ",{$tag->slug}";
    }

    return $url . ($query ? "?$query" : "");
  }

  /**
   * Return the title for an array of tags.
   * @param  array   array of Model_Tag objects
   */
  static function title($tags) {
    $name = array_shift($tags)->name;
    foreach ($tags as $tag) {
      $name .= ", {$tag->name}";
    }

    return t2("Tag: %tag_name", "Tags: %tag_name", count($tags) + 1, array("tag_name" => $name));
  }
}
