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
class Rss_Controller extends Controller {
  public static $page_size = 30;

  public function albums($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item)) {
      return Kohana::show_404();
    }

    $page = $this->input->get("page", 1);
    if ($page < 1) {
      url::redirect("rss/albums/{$item->id}");
    }

    $children = $item
      ->viewable()
      ->descendants(self::$page_size, ($page - 1) * self::$page_size, "photo");
    $max_pages = ceil($item->viewable()->descendants_count("photo") / self::$page_size);

    if ($page > $max_pages) {
      url::redirect("rss/albums/{$item->id}?page=$max_pages");
    }

    $view = new View("feed.mrss");
    $view->title = $item->title;
    $view->link = url::abs_site("albums/{$item->id}");
    $view->description = $item->description;
    $view->feed_link = url::abs_site("rss/albums/{$item->id}");
    $view->children = $children;

    if ($page > 1) {
      $previous_page = $page - 1;
      $view->previous_page_link = url::site("rss/albums/{$item->id}?page={$previous_page}");
    }

    if ($page < $max_pages) {
      $next_page = $page + 1;
      $view->next_page_link = url::site("rss/albums/{$item->id}?page={$next_page}");
    }

    // @todo do we want to add an upload date to the items table?
    $view->pub_date = date("D, d M Y H:i:s T");

    rest::http_content_type(rest::RSS);
    print $view;
  }

  public function updates() {
    $page = $this->input->get("page", 1);
    if ($page < 1) {
      url::redirect("rss/updates");
    }

    $orm = ORM::factory(item)
      ->viewable()
      ->where("type !=", "album")
      ->orderby("created", DESC);
    $items = $orm
      ->find_all(self::$page_size, ($page - 1) * self::$page_size);
    $max_pages = ceil($orm->count_last_query() / self::$page_size);

    if ($page > $max_pages) {
      url::redirect("rss/updates?page=$max_pages");
    }

    $view = new View("feed.mrss");
    $view->title = t("Recent Updates");
    $view->link = url::abs_site("albums/1");
    $view->description = $item->description;
    $view->feed_link = url::abs_site("rss/updates");
    $view->children = $items;

    if ($page > 1) {
      $previous_page = $page - 1;
      $view->previous_page_link = url::site("rss/updates?page={$previous_page}");
    }

    if ($page < $max_pages) {
      $next_page = $page + 1;
      $view->next_page_link = url::site("rss/updates?page={$next_page}");
    }

    // @todo do we want to add an upload date to the items table?
    $view->pub_date = date("D, d M Y H:i:s T");

    rest::http_content_type(rest::RSS);
    print $view;
  }

  public function tags($id) {
    $tag = ORM::factory("tag", $id);
    if (!$tag->loaded) {
      return Kohana::show_404();
    }

    $page = $this->input->get("page", 1);
    if ($page < 1) {
      url::redirect("rss/tags/{$tag->id}");
    }

    $children = $tag->items(self::$page_size, ($page - 1) * self::$page_size, "photo");
    $max_pages = ceil($tag->count / self::$page_size);

    if ($page > $max_pages) {
      url::redirect("rss/tags/{$tag->id}?page=$max_pages");
    }

    $view = new View("feed.mrss");
    $view->title = $tag->name;
    $view->link = url::abs_site("tags/{$tag->id}");
    $view->description = t("Photos related to %tag_name", array("tag_name" => $tag->name));
    $view->feed_link = url::abs_site("rss/tags/{$tag->id}");
    $view->children = $children;

    if ($page > 1) {
      $previous_page = $page - 1;
      $view->previous_page_link = url::site("rss/tags/{$tag->id}?page={$previous_page}");
    }

    if ($page < $max_pages) {
      $next_page = $page + 1;
      $view->next_page_link = url::site("rss/tags/{$tag->id}?page={$next_page}");
    }

    // @todo do we want to add an upload date to the items table?
    $view->pub_date = date("D, d M Y H:i:s T");

    rest::http_content_type(rest::RSS);
    print $view;
  }

  public function comments($id=null) {
    $page = $this->input->get("page", 1);
    if ($page < 1) {
      url::redirect("rss/comments/$id");
    }

    $orm = ORM::factory("comment")
      ->where("state", "published")
      ->orderby("created", "DESC");
    if (!empty($id)) {
      $orm->where("item_id", $id);
    }
                       
    $comments = $orm->find_all(self::$page_size, ($page - 1) * self::$page_size);
    $max_pages = ceil($orm->count_last_query() / self::$page_size);

    if ($max_pages && $page > $max_pages) {
      url::redirect("rss/comments/{$item->id}?page=$max_pages");
    }

    $view = new View("comment.mrss");
    $view->title = htmlspecialchars(t("Recent Comments"));
    $view->link = url::abs_site("albums/1");
    $view->description = t("Recent Comments");
    $view->feed_link = url::abs_site("rss/comments");
    $view->pub_date = date("D, d M Y H:i:s T");
   
    $view->children = array();
    foreach ($comments as $comment) {
      $item = $comment->item();
      $view->children[] = array(
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

    if ($page > 1) {
      $previous_page = $page - 1;
      $view->previous_page_link = url::site("rss/comments/{$item->id}?page={$previous_page}");
    }

    if ($page < $max_pages) {
      $next_page = $page + 1;
      $view->next_page_link = url::site("rss/comments/{$item->id}?page={$next_page}");
    }

    rest::http_content_type(rest::RSS);
    print $view;
  }
}