<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Menu_Element {
  public $label;
  public $url;
  public $css_id;
  public $id;

  /**
   * Set the id
   * @chainable
   */
  public function id($id) {
    $this->id = $id;
    return $this;
  }

  /**
   * Set the label
   * @chainable
   */
  public function label($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * Set the url
   * @chainable
   */
  public function url($url) {
    $this->url = $url;
    return $this;
  }

  /**
   * Set the css id
   * @chainable
   */
  public function css_id($css_id) {
    $this->css_id = $css_id;
    return $this;
  }

}

/**
 * Menu element that provides a link to a new page.
 */
class Menu_Element_Link extends Menu_Element {
  public function __toString() {
    if (isset($this->css_id) && !empty($this->css_id)) {
      $css_id = " id=\"$this->css_id\"";
    } else {
      $css_id = "";
    }
    return "<li><a$css_id class=\"gMenuElement\" href=\"$this->url\">$this->label</a></li>";
  }
}

/**
 * Menu element that provides a pop-up dialog
 */
class Menu_Element_Dialog extends Menu_Element {
  public function __toString() {
    return "<li><a class=\"gMenuLink\" href=\"$this->url\" " .
           "title=\"$this->label\">$this->label</a></li>";
  }
}

/**
 * Root menu or submenu
 */
class Menu_Core extends Menu_Element {
  public $elements;
  public $is_root;

  /**
   * Return an instance of a Menu_Element
   * @chainable
   */
  public static function factory($type) {
    switch($type) {
    case "link":
      return new Menu_Element_Link();

    case "dialog":
      return new Menu_Element_Dialog();

    case "submenu":
      return new Menu();

    default:
      throw Exception("@todo UNKNOWN_MENU_TYPE");
    }
  }

  public function __construct($is_root=false) {
    $this->elements = array();
    $this->is_root = $is_root;
  }

  /**
   * Add a new element to this menu
   */
  public function append($menu_element) {
    $this->elements[$menu_element->id] = $menu_element;
    return $this;
  }

  /**
   * Retrieve a Menu_Element by id
   */
  public function get($id) {
    return $this->elements[$id];
  }

  public function __toString() {
    $html = $this->is_root ? "<ul class=\"gMenu\">" : "<li><a href=#>$this->label</a><ul class=\"gMenu\">";
    $html .= implode("\n", $this->elements);
    $html .= $this->is_root ? "</ul>" : "</ul></li>";
    return $html;
  }
}
