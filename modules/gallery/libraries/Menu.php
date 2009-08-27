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
class Menu_Element {
  public $label;
  public $url;
  public $css_id;
  public $css_class;
  public $id;
  public $type;

  public function __construct($type) {
    $this->type = $type;
  }

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

  /**
   * Set the css class
   * @chainable
   */
  public function css_class($css_class) {
    $this->css_class = $css_class;
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
    if (isset($this->css_class) && !empty($this->css_class)) {
      $css_class = " $this->css_class";
    } else {
      $css_class = "";
    }
    return "<li><a$css_id class=\"gMenuLink $css_class\" href=\"$this->url\" " .
      "title=\"$this->label\">$this->label</a></li>";
  }
}

/**
 * Menu element that provides an AJAX link.
 */
class Menu_Element_Ajax_Link extends Menu_Element {
  public $ajax_handler;

  /**
   * Set the AJAX handler
   * @chainable
   */
  public function ajax_handler($ajax_handler) {
    $this->ajax_handler = $ajax_handler;
    return $this;
  }

  public function __toString() {
    if (isset($this->css_id) && !empty($this->css_id)) {
      $css_id = " id=\"$this->css_id\"";
    } else {
      $css_id = "";
    }
    if (isset($this->css_class) && !empty($this->css_class)) {
      $css_class = " $this->css_class";
    } else {
      $css_class = "";
    }
    return "<li><a$css_id class=\"gAjaxLink $css_class\" href=\"$this->url\" " .
      "title=\"$this->label\" ajax_handler=\"$this->ajax_handler\">$this->label</a></li>";
  }
}

/**
 * Menu element that provides a pop-up dialog
 */
class Menu_Element_Dialog extends Menu_Element {
  public function __toString() {
    if (isset($this->css_id) && !empty($this->css_id)) {
      $css_id = " id=\"$this->css_id\"";
    } else {
      $css_id = "";
    }
    if (isset($this->css_class) && !empty($this->css_class)) {
      $css_class = " $this->css_class";
    } else {
      $css_class = "";
    }
    return "<li><a$css_id class=\"gDialogLink $css_class\" href=\"$this->url\" " .
           "title=\"$this->label\">$this->label</a></li>";
  }
}

/**
 * Root menu or submenu
 */
class Menu_Core extends Menu_Element {
  public $elements;
  public $is_root = false;

  /**
   * Return an instance of a Menu_Element
   * @chainable
   */
  public static function factory($type) {
    switch($type) {
    case "link":
      return new Menu_Element_Link($type);

    case "ajax_link":
      return new Menu_Element_Ajax_Link($type);

    case "dialog":
      return new Menu_Element_Dialog($type);

    case "root":
      $menu = new Menu("root");
      $menu->css_class("gMenu");
      return $menu;

    case "submenu":
      return new Menu("submenu");

    default:
      throw Exception("@todo UNKNOWN_MENU_TYPE");
    }
  }

  public function compact() {
    foreach ($this->elements as $target_id => $element) {
      if ($element->type == "submenu") {
        if (empty($element->elements)) {
          $this->remove($target_id);
        } else {
          $element->compact();
        }
      }
    }
    return $this;
  }

  public function __construct($type) {
    parent::__construct($type);
    $this->elements = array();
    $this->is_root = $type == "root";
  }

  /**
   * Add a new element to this menu
   */
  public function append($menu_element) {
    $this->elements[$menu_element->id] = $menu_element;
    return $this;
  }

  /**
   * Add a new element to this menu
   */
  public function add_after($target_id, $new_menu_element) {
    $copy = array();
    foreach ($this->elements as $id => $menu_element) {
      $copy[$id] = $menu_element;
      if ($id == $target_id) {
        $copy[$new_menu_element->id] = $new_menu_element;
      }
    }
    $this->elements = $copy;
    return $this;
  }

  /**
   * Remove an element from the menu
   */
  public function remove($target_id) {
    unset($this->elements[$target_id]);
  }

  /**
   * Retrieve a Menu_Element by id
   */
  public function get($id) {
    if (array_key_exists($id, $this->elements)) {
      return $this->elements[$id];
    }

    return null;
  }

  public function __toString() {
    $html = $this->is_root ? "<ul class=\"$this->css_class\">" :
      "<li title=\"$this->label\"><a href=\"#\">$this->label</a><ul>";
    $html .= implode("\n", $this->elements);
    $html .= $this->is_root ? "</ul>" : "</ul></li>";
    return $html;
  }
}
