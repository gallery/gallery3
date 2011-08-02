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
  private $id = 0;
  public $class;

  static function build_from_item($item) {
    $breadcrumbs = array();
    if ($item->id != item::root()->id) {
      $elements = $item->parents()->as_array();
      $elements[] = $item;

      foreach ($elements as $element) {
        $breadcrumbs[] = new Breadcrumb($element->title, $element->url(), $element->id);
      }
    }

    return self::prepare_for_render($breadcrumbs);
  }

  /**
   * This static function takes a list (variable arguments) of Breadcrumbs and builds a dynamic
   * breadcrumb list.  Used to create a bredcrumb for dynamic albums. Will really be useful
   * for the display context change.
   */
  static function build_from_list() {
    return self::prepare_for_render(func_get_args());
  }

  private static function prepare_for_render($breadcrumbs) {
    if (!empty($breadcrumbs)) {
      $class = "g-active";

      end($breadcrumbs);
      while ($breadcrumb = current($breadcrumbs)) {
        $breadcrumb->class = $class;
        $class = "";
        $breadcrumb->url =  $breadcrumb->url .
          (isset($last_id) && $last_id > 0 ? "?show={$last_id}" : "");
        $last_id = $breadcrumb->id;
        $breadcrumb = prev($breadcrumbs);
      }
      $breadcrumbs[0]->class = "g-first";
    }
    return $breadcrumbs;
  }

  public function __construct($title, $url, $id=0) {
    $this->title = $title;
    $this->url = $url;
    $this->id = $id;
  }

  public function render() {
    $view = new View("breadcrumb_link.html");
    $view->breadcrumb = $this;
    return $view;
  }

  public function __toString() {
    return (String)$this->render();
  }
}
