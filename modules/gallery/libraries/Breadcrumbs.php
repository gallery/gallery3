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
class Breadcrumbs  {
  private $elements;

  static function instance() {
    return new Breadcrumbs();
  }

  private function __construct() {
    $elements = new ArrayObject();
  }

  public function append_parents($parents) {
    foreach ($parents as $item) {
      $this->elements[] = Breadcrumb::instance($item->title, $item->relative_path())->id($item->id);
    }
    return $this;
  }

  public function append_item($item, $query=null) {
    $this->elements[] = Breadcrumb::instance($item->title, $item->relative_path())
      ->id($item->id)->query($query);
    return $this;
  }

  public function append_dynamic($title, $url, $query=null) {
    $element = Breadcrumb::instance($item->title, $item->relative_path())->query($query);
    $this->elements[] = $element;
    return $this;
  }

  public function as_array() {
    return (array)$this->elements;
  }

}

class Breadcrumb {
  private $title;
  private $url;
  private $query = null;
  private $id = 0;

  static function instance($title, $url) {
    return new Breadcrumb($title, $url);
  }
  private function __construct($title, $url) {
    $this->title = $title;
    $this->url = $url;
  }

  public function is_item_parent($item) {
    return !empty($item) && $item->parent_id == $this->id;
  }

  public function id($id) {
    $this->id = $id;
    return $this;
  }

  public function query($query) {
    $this->query = $query;
    return $this;
  }

  public function url() {
    $url = url::site($this->url);
    if ($this->query) {
      $url .= "?{$this->query}";
    }
    return $url;
  }

  public function title() {
    return html::purify(text::limit_chars($this->title,
                    module::get_var("gallery", "visible_title_length")));
  }
}

