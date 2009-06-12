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
  static function available_feeds($item) {
    return array(array("description" => t("All new comments"),
                       "sidebar" => true,
                       "uri" => "comments"),
                 array("description" => sprintf(t("Comments on %s"), $item->title),
                       "sidebar" => true,
                       "uri" => "comments/{$item->id}"));
  }

  static function comments($offset, $limit, $id) {
    $feed = new stdClass();
    $orm = ORM::factory("comment")
      ->where("state", "published")
      ->orderby("created", "DESC");
    if (!empty($id)) {
      $orm->where("item_id", $id);
    }

    $feed->view = "comment.mrss";
    $comments = $orm->find_all($limit, $offset);
    $feed->data["children"] = array();
    foreach ($comments as $comment) {
      $item = $comment->item();
      $feed->data["children"][] = array(
        "pub_date" => date("D, d M Y H:i:s T", $comment->created),
        "text" => htmlspecialchars($comment->text),
        "thumb_url" => $item->thumb_url(),
        "thumb_height" => $item->thumb_height,
        "thumb_width" => $item->thumb_width,
        "item_link" => htmlspecialchars(url::abs_site("{$item->type}s/$item->id")),
        "title" =>htmlspecialchars($item->title),
        "author" =>
          empty($comment->guest_name) ? $comment->author()->full_name : $comment->guest_name
      );
    }

    $feed->max_pages = ceil($comments->count() / $limit);
    $feed->data["title"] = htmlspecialchars(t("Recent Comments"));
    $feed->data["link"] = url::abs_site("albums/" . (empty($id) ? "1" : $id));
    $feed->data["description"] = t("Recent Comments");

    return $feed;
  }
}