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
class G2_Controller extends Admin_Controller {
  /**
   * Redirect Gallery 2 urls to their appropriate matching Gallery 3 url.
   *
   * Inputs look like this:
   *   /g2/map?url=v/Family/Wedding/IMG_3.jpg.html
   *   /g2/map?id=1931
   */
  public function map() {
    $input = Input::instance();
    if ($g2_id = $input->get("id")) {
      $where = array("g2_id", "=", $g2_id);
    } else if ($g2_url = $input->get("url")) {
      $where = array("g2_url", "=", $g2_url);
    } else {
      throw new Kohana_404_Exception();
    }

    $g2_map = ORM::factory("g2_map")
      ->merge_where(array($where))
      ->find();

    if (!$g2_map->loaded()) {
      throw new Kohana_404_Exception();
    }

    $item = ORM::factory("item")->where("id", "=", $g2_map->g3_id)->find();
    if (!$item->loaded() || !access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }


    // Redirect the user to the new url
    switch ($g2_map->resource_type) {
    case "thumbnail":
      url::redirect($item->thumb_url(true));

    case "resize":
      url::redirect($item->resize_url(true));

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