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
class Media_RSS_Controller extends Controller {
  public static $page_size = 10;

  public function feed($id) {
    $item = ORM::factory("item", $id)->find();
    if (!$item->loaded) {
      return Kohana::show_404();
    }

    $page = $this->input->get("page", 1);
    if ($page < 1) {
      url::redirect("media_rss/feed/{$item->id}");
    }

    $children = $item->descendants(self::$page_size, ($page - 1) * self::$page_size, "photo");
    $count = $item->descendants_count("photo");
    $max_pages = ceil($item->descendants_count("photo") / self::$page_size);

    if ($page > $max_pages) {
      url::redirect("media_rss/feed/{$item->id}?page=$max_pages");
    }

    $view = new View("feed.mrss");
    $view->item = $item;
    $view->children = $children;
    $view->previous_page = $page > 1 ? $page - 1 : null;
    $view->next_page = $page < $max_pages ? $page + 1 : null;

    // @todo do we want to add an upload date to the items table?
    $view->pub_date = date("D, d M Y H:i:s T");

    rest::http_content_type(rest::RSS);
    print $view;
  }
}