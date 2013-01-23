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

class gallery_rss_Core {
  static function available_feeds($item, $tag) {
    $feeds["gallery/latest"] = t("Latest photos and movies");

    if ($item) {
      $feed_item = $item -> is_album() ? $item : $item->parent();

      $feeds["gallery/album/{$feed_item->id}"] =
          t("%title photos and movies", array("title" => $feed_item->title));
    }

    return $feeds;
  }

  static function feed($feed_id, $offset, $limit, $id) {
    $feed = new stdClass();
    switch ($feed_id) {
    case "latest":
      $feed->items = ORM::factory("item")
        ->viewable()
        ->where("type", "<>", "album")
        ->order_by("created", "DESC")
        ->find_all($limit, $offset);

      $all_items = ORM::factory("item")
        ->viewable()
        ->where("type", "<>", "album")
        ->order_by("created", "DESC");

      $feed->max_pages = ceil($all_items->find_all()->count() / $limit);
      $feed->title = t("%site_title - Recent updates", array("site_title" => item::root()->title));
      $feed->description = t("Recent updates");
      return $feed;

    case "album":
      $item = ORM::factory("item", $id);
      access::required("view", $item);

      $feed->items = $item
        ->viewable()
        ->descendants($limit, $offset, array(array("type", "=", "photo")));
      $feed->max_pages = ceil(
        $item->viewable()->descendants_count(array(array("type", "=", "photo"))) / $limit);
      if ($item->id == item::root()->id) {
        $feed->title = html::purify($item->title);
      } else {
        $feed->title = t("%site_title - %item_title",
                         array("site_title" => item::root()->title,
                               "item_title" => $item->title));
      }
      $feed->description = nl2br(html::purify($item->description));

      return $feed;
    }
  }
}
