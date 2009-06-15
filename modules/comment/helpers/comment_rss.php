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

class comment_rss_Core {
  static function available_feeds($item, $tag) {
    $feeds["comment/newest"] = t("All new comments");
    if ($item) {
      $feeds["comment/item/$item->id"] =
        t("Comments on %title", array("title" => p::clean($item->title)));
    }
    return $feeds;
  }

  static function feed($feed_id, $offset, $limit, $id) {
    switch ($feed_id) {
    case "newest":
      $comments = ORM::factory("comment")
        ->where("state", "published")
        ->orderby("created", "DESC");
      $all_comments = ORM::factory("comment")
        ->where("state", "published")
        ->orderby("created", "DESC");
      break;

    case "item":
      $comments = ORM::factory("comment")
        ->where("state", "published")
        ->orderby("created", "DESC")
        ->where("item_id", $id);
      $all_comments = ORM::factory("comment")
        ->where("state", "published")
        ->where("item_id", $id);
    }

    if (!empty($comments)) {
      $feed->view = "comment.mrss";
      $comments = $comments->find_all($limit, $offset);
      $feed->children = array();
      foreach ($comments as $comment) {
        $item = $comment->item();
        $feed->children[] = new ArrayObject(
          array("pub_date" => date("D, d M Y H:i:s T", $comment->created),
                "text" => $comment->text,
                "thumb_url" => $item->thumb_url(),
                "thumb_height" => $item->thumb_height,
                "thumb_width" => $item->thumb_width,
                "item_uri" => url::abs_site("{$item->type}s/$item->id"),
                "title" => $item->title,
                "author" => $comment->author_name()),
          ArrayObject::ARRAY_AS_PROPS);
      }

      $feed->max_pages = ceil($all_comments->find_all()->count() / $limit);
      $feed->title = htmlspecialchars(t("Recent Comments"));
      $feed->uri = url::abs_site("albums/" . (empty($id) ? "1" : $id));
      $feed->description = t("Recent Comments");

      return $feed;
    }
  }
}