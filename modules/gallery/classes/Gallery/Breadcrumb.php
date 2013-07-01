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
class Gallery_Breadcrumb {
  public $title;
  public $url;
  public $first;
  public $last;

  static function factory($title, $url) {
    return new Breadcrumb($title, $url);
  }

  public function __construct($title, $url) {
    $this->title = $title;
    $this->url = $url;
    $this->first = false;
    $this->last = false;
  }

  /**
   * Return an array of Breadcrumb instances build from the parents of a given item.
   * The first and last Breadcrumb instances will be marked first/last as appropriate.
   * Each breadcrumb will have a ?show= query parameter that refers to the id of the next
   * item in line.
   *
   * @return array Breadcrumb instances
   */
  static function array_from_item_parents($item, $last_breadcrumbs=array()) {
    if ($item->is_root() && !$last_breadcrumbs) {
      return array();
    }

    $bc = array_merge($item->parents->find_all()->as_array(), array($item));
    for ($i = 0; $i < count($bc) - 1; $i++) {
      $bc[$i] = Breadcrumb::factory($bc[$i]->title, $bc[$i]->url("show={$bc[$i+1]->id}"));
    }
    $bc[$i] = Breadcrumb::factory($item->title, $item->url());
    $bc = array_merge($bc, $last_breadcrumbs);

    $bc[0]->set_first();
    end($bc)->set_last();
    return $bc;
  }

  public function set_first() {
    $this->first = true;
    return $this;
  }

  public function set_last() {
    $this->last = true;
    return $this;
  }
}
