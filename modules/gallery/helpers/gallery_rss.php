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

class gallery_rss_Core {
  static function available_feeds($item, $tag) {
    $feeds["gallery/latest"] = t("Latest photos and movies");
    return $feeds;
  }

  static function feed($feed_id, $offset, $limit, $id) {
    switch ($feed_id) {
    case "latest":
      $feed->children = ORM::factory("item")
        ->viewable()
        ->where("type !=", "album")
        ->orderby("created", "DESC")
        ->find_all($limit, $offset);

      $all_children = ORM::factory("item")
        ->viewable()
        ->where("type !=", "album")
        ->orderby("created", "DESC");

      $feed->max_pages = ceil($all_children->find_all()->count() / $limit);
      $feed->title = t("Recent Updates");
      $feed->description = t("Recent Updates");
      return $feed;

    case "album":
      $item = ORM::factory("item", $id);
      access::required("view", $item);

      $feed->children = $item
        ->viewable()
        ->descendants($limit, $offset, array("type" => "photo"));
      $feed->max_pages = ceil(
        $item->viewable()->descendants_count(array("type" => "photo")) / $limit);
      $feed->title = html::purify($item->title);
      $feed->description = nl2br(html::purify($item->description));

      return $feed;
    }
  }
}
