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
  private $_title;
  private $_url;
  private $_query = null;
  private $_id = 0;

  static function for_item($item) {
    $breadcrumbs = array();
    if ($item->id != 1) {
      $breadcrumbs[] = Breadcrumb::instance($item->title, $item->relative_path())->id($item->id);
      foreach (array_reverse($item->parents()->as_array()) as $parent) {
        $breadcrumb = Breadcrumb::instance($parent->title, $parent->relative_path())->id($parent->id);
        if (!empty($breadcrumbs)) {
          $breadcrumb->query("show={$breadcrumbs[0]->_id}");
        }
        array_unshift($breadcrumbs, $breadcrumb);
      }
    }

    return (array)$breadcrumbs;
  }

  /**
   * This static function takes a list (variable arguments) of Breadcrumbs and builds a dynamic
   * breadcrumb list.  Used to create a bredcrumb for dynamic albums. Will really be useful
   * for the display context change.
   */
  static function build() {
    $breadcrumbs = array();
    foreach (array_reverse(func_get_args()) as $breadcrumb) {
      if (!empty($breadcrumbs) && $breadcrumb->_id > 0) {
        $breadcrumb->query("show={$breadcrumbs[0]->_id}");
      }
      array_unshift($breadcrumbs, $breadcrumb);
    }
    return (array)$breadcrumbs;
  }

  static function instance($title, $url) {
    return new Breadcrumb($title, $url);
  }

  private function __construct($title, $url) {
    $this->_title = $title;
    $this->_url = $url;
  }

  public function is_item_parent($item) {
    return !empty($item) && $item->parent_id == $this->_id;
  }

  public function id($id) {
    $this->_id = $id;
    return $this;
  }

  public function query($query) {
    $this->_query = $query;
    return $this;
  }

  public function url() {
    $url = url::site($this->_url);
    if (!empty($this->_query)) {
      $url .= "?{$this->_query}";
    }
    return $url;
  }

  public function title() {
    return $this->_title;
  }
}

