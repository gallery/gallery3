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
class Rss_Controller extends Controller {
  public static $page_size = 30;

  public function feed($method, $id=null) {
    $page = $this->input->get("page", 1);
    $feed_uri = "rss/feed/$method" . (empty($id) ? "" : "/$id");
    if ($page < 1) {
      url::redirect($feed_uri);
    }

    $feed = rss::process_feed($method, ($page - 1) * self::$page_size, self::$page_size, $id);
    if ($feed->max_pages && $page > $feed->max_pages) {
      url::redirect("$feed_uri?page={$feed->max_pages}");
    }

    $view = new View(empty($feed->view) ? "feed.mrss" : $feed->view);
    foreach ($feed->data as $field => $value) {
      $view->$field = $value;
    }
    $view->feed_link = url::abs_site($feed_uri);

    if ($page > 1) {
      $previous_page = $page - 1;
      $view->previous_page_link = url::site("$feed_uri?page={$previous_page}");
    }

    if ($page < $feed->max_pages) {
      $next_page = $page + 1;
      $view->next_page_link = url::site("$feed_uri?page={$next_page}");
    }

    $view->pub_date = date("D, d M Y H:i:s T");

    rest::http_content_type(rest::RSS);
    print $view;
  }
}