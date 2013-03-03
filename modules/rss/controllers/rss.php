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
class Rss_Controller extends Controller {
  public static $page_size = 20;

  public function feed($module_id, $feed_id, $id=null) {
    $page = (int) Input::instance()->get("page", 1);
    if ($page < 1) {
      url::redirect(url::merge(array("page" => 1)));
    }

    // Configurable page size between 1 and 100, default 20
    $page_size = max(1, min(100, (int) Input::instance()->get("page_size", self::$page_size)));

    // Run the appropriate feed callback
    if (module::is_active($module_id)) {
      $class_name = "{$module_id}_rss";
      if (class_exists($class_name) && method_exists($class_name, "feed")) {
        $feed = call_user_func(
          array($class_name, "feed"), $feed_id,
          ($page - 1) * $page_size, $page_size, $id);
      }
    }
    if (empty($feed)) {
      throw new Kohana_404_Exception();
    }

    if ($feed->max_pages && $page > $feed->max_pages) {
      url::redirect(url::merge(array("page" => $feed->max_pages)));
    }

    $view = new View(empty($feed->view) ? "feed.mrss" : $feed->view);
    unset($feed->view);

    $view->feed = $feed;
    $view->pub_date = date("D, d M Y H:i:s O");

    $feed->uri = url::abs_site(url::merge($_GET));
    if ($page > 1) {
      $feed->previous_page_uri = url::abs_site(url::merge(array("page" => $page - 1)));
    }
    if ($page < $feed->max_pages) {
      $feed->next_page_uri = url::abs_site(url::merge(array("page" => $page + 1)));
    }

    header("Content-Type: application/rss+xml");
    print $view;
  }
}