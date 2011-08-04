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
class Breadcrumb_Core {
  public $title;
  public $url;
  public $id;
  public $first;
  public $last;

  static function build_from_item($item) {
    $breadcrumbs = array();
    foreach ($item->parents() as $element) {
      $breadcrumbs[] = new Breadcrumb($element->title, $element->url(), $element->id);
    }

    if (!empty($breadcrumbs)) {
      $breadcrumbs[] = new Breadcrumb($item->title, $item->url(), $item->id);
    }

    return self::generate_show_query_strings($breadcrumbs);
  }

  /**
   * This static function takes a list (variable arguments) of Breadcrumbs and builds a dynamic
   * breadcrumb list.  Used to create a breadcrumb for dynamic albums. Will really be useful
   * for the display context change.
   */
  static function build_from_list() {
    return self::generate_show_query_strings(func_get_args());
  }

  private static function generate_show_query_strings($breadcrumbs) {
    if (!empty($breadcrumbs)) {

      end($breadcrumbs)->last = true;;
      while ($breadcrumb = current($breadcrumbs)) {
        if (isset($last_id) && $last_id > 0) {
          $query = parse_url($breadcrumb->url, PHP_URL_QUERY);
          $breadcrumb->url =  $breadcrumb->url . ($query ? "&" : "?") . "show={$last_id}";
        }
        $last_id = $breadcrumb->id;
        $breadcrumb = prev($breadcrumbs);
      }
      $breadcrumbs[0]->first = true;
    }

    return $breadcrumbs;
  }

  public function __construct($title, $url, $id=0) {
    $this->title = $title;
    $this->url = $url;
    $this->id = $id;
    $this->first = false;
    $this->last = false;
  }
}
