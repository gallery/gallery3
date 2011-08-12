<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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

class Search_Display_Context_Core extends Display_Context {
  protected function __construct() {
    parent::__construct("search");
  }

  function display_context($item) {
    $position = search::get_position($item, $this->get("query_terms"));

    if ($position > 1) {
      list ($count, $result_data) =
        search::search($this->get("query_terms"), 3, $position - 2);
      list ($previous_item, $ignore, $next_item) = $result_data;
    } else {
      $previous_item = null;
      list ($count, $result_data) = search::search($this->get("query_terms"), 1, $position);
      list ($next_item) = $result_data;
    }

    $q = $this->get("q");
    $search_url = url::abs_site("search?q=" . urlencode($q) . "&show={$item->id}");
    $root = item::root();

    return array("position" =>$position,
                 "previous_item" => $previous_item,
                 "next_item" =>$next_item,
                 "sibling_count" => $count,
                 "breadcrumbs" => array(
                                  Breadcrumb::instance($root->title, "/", $root->id),
                                  Breadcrumb::instance($q, $search_url),
                                  Breadcrumb::instance($item->title, $item->url())));
  }
}
