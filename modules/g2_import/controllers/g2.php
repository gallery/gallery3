<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class G2_Controller extends Controller {
  /**
   * Redirect Gallery 2 urls to their appropriate matching Gallery 3 url.
   *
   * We use mod_rewrite to create this path, so Gallery2 urls like this:
   *   /gallery2/v/Family/Wedding.jpg.html
   *   /gallery2/main.php?g2_view=core.ShowItem&g2_itemId=1234
   *
   * Show up here like this:
   *   /g2/map?path=v/Family/Wedding.jpg.html
   *   /g2/map?g2_view=core.ShowItem&g2_itemId=1931
   */
  public function map() {
    $input = Input::instance();
    $path = $input->get("path");
    $id = $input->get("g2_itemId");

    if ($path || $id) {
      if ($id) {
        // Requests by id are either core.DownloadItem or core.ShowItem requests. Later versions of
        // Gallery 2 don't specify g2_view if it's the default (core.ShowItem). And in some cases
        // (bbcode, embedding) people are using the id style URLs although URL rewriting is enabled.
        $where = array(array("g2_id", "=", $id));
        $view = $input->get("g2_view");
        if ($view) {
          $where[] = array("g2_url", "like", "%g2_view=$view%");
        } // else: Assuming that the first search hit is sufficiently good.
      } else if ($path) {
        $where = array(array("g2_url", "IN", array($path, str_replace(" ", "+", $path))));
      } else {
        throw new Kohana_404_Exception();
      }

      $g2_map = ORM::factory("g2_map")
        ->merge_where($where)
        ->find();

      if (!$g2_map->loaded()) {
        throw new Kohana_404_Exception();
      }

      $item = ORM::factory("item", $g2_map->g3_id);
      if (!$item->loaded()) {
        throw new Kohana_404_Exception();
      }
      $resource_type = $g2_map->resource_type;
    } else {
      $item = item::root();
      $resource_type = "album";
    }
    access::required("view", $item);


    // Redirect the user to the new url
    switch ($resource_type) {
    case "thumbnail":
      url::redirect($item->thumb_url(true));

    case "resize":
      url::redirect($item->resize_url(true));

    case "file":
    case "full":
      url::redirect($item->file_url(true));

    case "item":
    case "album":
      url::redirect($item->abs_url());

    case "group":
    case "user":
    default:
      throw new Kohana_404_Exception();
    }
  }
}