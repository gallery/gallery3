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

class Item_Display_Context_Core extends Display_Context {
  protected function __construct() {
    parent::__construct("item");
  }

  function display_context($item) {
    $where = array(array("type", "!=", "album"));
    $position = item::get_position($item, $where);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) =
        $item->parent()->viewable()->children(3, $position - 2, $where);
    } else {
      $previous_item = null;
      list ($next_item) = $item->parent()->viewable()->children(1, $position, $where);
   }

    return array("position" =>$position,
                 "previous_item" => $previous_item,
                 "next_item" =>$next_item,
                 "sibling_count" => $item->parent()->viewable()->children_count($where),
                 "parents" => $item->parents()->as_array(),
                 "breadcrumbs" => Breadcrumb::array_from_item_parents($item));
  }
}
