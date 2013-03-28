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

class comment_rss_Core {
  static function feed_visible($feed_id) {
    $visible = module::get_var("comment", "rss_visible");
    if (!in_array($feed_id, array("newest", "per_item"))) {
      return false;
    }

    return ($visible == "all" || $visible == $feed_id);
  }

  static function available_feeds($item, $tag) {
    $feeds = array();

    if (comment_rss::feed_visible("newest")) {
      $feeds["comment/newest"] = t("All new comments");
    }

    if ($item && comment_rss::feed_visible("per_item")) {
      $feeds["comment/per_item/$item->id"] =
        t("Comments on %title", array("title" => html::purify($item->title)));
    }
    return $feeds;
  }

  static function feed($feed_id, $offset, $limit, $id) {
    if (!comment_rss::feed_visible($feed_id)) {
      return;
    }

    $comments = ORM::factory("comment")
      ->viewable()
      ->where("comments.state", "=", "published")
      ->order_by("comments.created", "DESC");

    if ($feed_id == "item") {
      $item = ORM::factory("item", $id);
      $comments
        ->where("items.left_ptr", ">=", $item->left_ptr)
        ->where("items.right_ptr", "<=", $item->right_ptr);
    }

    $feed = new stdClass();
    $feed->view = "comment.mrss";
    $feed->comments = array();
    foreach ($comments->find_all($limit, $offset) as $comment) {
      $item = $comment->item();
      $feed->comments[] = new ArrayObject(
        array("pub_date" => date("D, d M Y H:i:s O", $comment->created),
              "text" => nl2br(html::purify($comment->text)),
              "thumb_url" => $item->thumb_url(),
              "thumb_height" => $item->thumb_height,
              "thumb_width" => $item->thumb_width,
              "item_uri" => url::abs_site("{$item->type}s/$item->id"),
              "title" => (
                ($item->id == item::root()->id) ?
                html::purify($item->title) :
                t("%site_title - %item_title",
                  array("site_title" => item::root()->title,
                        "item_title" => $item->title))),
              "author" => html::clean($comment->author_name())),
        ArrayObject::ARRAY_AS_PROPS);
    }

    $feed->max_pages = ceil($comments->count_all() / $limit);
    $feed->title = html::purify(t("%site_title - Recent Comments",
                                 array("site_title" => item::root()->title)));
    $feed->uri = url::abs_site("albums/" . (empty($id) ? "1" : $id));
    $feed->description = t("Recent comments");

    return $feed;
  }
}
