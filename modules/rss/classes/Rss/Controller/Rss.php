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
class Rss_Controller_Rss extends Controller {
  public static $page_size = 20;

  public function action_feed() {
    $module_id = $this->request->arg(0, "alpha_dash");
    $feed_id = $this->request->arg(1);
    $id = $this->request->arg_optional(2);

    $page = (int) Arr::get($this->request->query(), "page", 1);
    if ($page < 1) {
      $this->redirect($this->_paginator_url(1, true));
    }

    // Configurable page size between 1 and 100, default 20
    $page_size = max(1, min(100, (int) Arr::get($this->request->query(), "page_size", static::$page_size)));

    // Run the appropriate feed callback
    if (Module::is_active($module_id)) {
      $feed = Gallery::module_hook($module_id, "Rss", "feed",
        array($feed_id, ($page - 1) * $page_size, $page_size, $id));
    }
    if (empty($feed)) {
      throw HTTP_Exception::factory(404);
    }

    if ($feed->max_pages && $page > $feed->max_pages) {
      $this->redirect($this->_paginator_url($feed->max_pages, true));
    }

    $view = new View(empty($feed->view) ? "rss/feed.mrss" : $feed->view);
    unset($feed->view);

    $view->feed = $feed;
    $view->pub_date = date("D, d M Y H:i:s O");

    $feed->uri = $this->_paginator_url($page);
    if ($page > 1) {
      $feed->previous_page_uri = $this->_paginator_url($page - 1);
    }
    if ($page < $feed->max_pages) {
      $feed->next_page_uri = $this->_paginator_url($page + 1);
    }

    $this->response->headers("Content-Type", "application/rss+xml; charset=UTF-8");
    $this->response->body($view);
  }

  protected function _paginator_url($page=1, $absolute=false) {
    return Request::current()->url($absolute) . URL::query(array("page" => $page));
  }
}
