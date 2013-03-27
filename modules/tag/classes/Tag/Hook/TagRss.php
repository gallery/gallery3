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

class tag_rss_Core {
  static function available_feeds($item, $tag) {
    if ($tag) {
      $feeds["tag/tag/{$tag->id}"] =
        t("Tag feed for %tag_name", array("tag_name" => $tag->name));
      return $feeds;
    }
    return array();
  }

  static function feed($feed_id, $offset, $limit, $id) {
    if ($feed_id == "tag") {
      $tag = ORM::factory("tag", $id);
      if (!$tag->loaded()) {
        throw new Kohana_404_Exception();
      }

      $feed = new stdClass();
      $feed->items = $tag->items($limit, $offset, "photo");
      $feed->max_pages = ceil($tag->count / $limit);
      $feed->title = t("%site_title - %tag_name",
                       array("site_title" => item::root()->title, "tag_name" => $tag->name));
      $feed->description = t("Photos related to %tag_name", array("tag_name" => $tag->name));

      return $feed;
    }
  }
}
