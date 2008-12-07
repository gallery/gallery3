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

class Menu_Core {
  protected $_text;
  protected $_url;
  protected $_items = array();

  public function __construct($text="", $url="#") {
    $this->_text = $text;
    $this->_url = $url;
  }

  public function append($menu_item) {
    $this->_items[] = $menu_item;
  }

  public function get($text) {
    foreach ($this->_items as $item) {
      if ($item->_text == $text) {
        return $item;
      }
    }
    return false;
  }

  private function _get_index($text) {
    foreach ($this->_items as $idx => $item) {
      if ($item->_text == $text) {
        return (int)$idx;
      }
    }
    return false;
  }

  public function insert_before($text, $menu_item) {
    $offset = $this->_get_index($text);

    $front_part = ($offset == 0) ? array() : array_splice($this->_items, 0, $offset);
    $back_part = ($offset == 0) ? $this->_items : array_splice($this->_items, $offset - 1);
    $this->_items = array_merge($front_part, array($menu_item), $back_part);
  }

  public function insert_after($text, $menu_item) {
    $offset = $this->_get_index($text);
    $last_offset = count($this->_items) - 1;
    // If not found, then append to the end
    if ($offset == false) {
      $offset = $last_offset;
    }

    $front_part = ($offset == $last_offset) ? $this->_items : array_splice($this->_items, 0, $offset + 1);
    Kohana::log("debug", print_r($front_part, 1));
    $back_part = ($offset == $last_offset) ? array() : array_splice($this->_items,  $offset - 1);
    Kohana::log("debug", print_r($back_part, 1));
    $this->_items = array_merge($front_part, array($menu_item), $back_part);
  }

  public function __toString() {
    $items_html = array();
    if (!empty($this->_text)) {
      if ($this->_url[0] == "#") {
        $class = "class=\"gDialogLink\"";
        $url = substr($this->_url, 1);
      } else {
        $class = "";
        $url = $this->_url;
      }

      $items_html[] = "<li><a $class href=\"$url\">$this->_text</a>";
    }

    if (!empty($this->_items)) {
      $items_html[] = "<ul>";

      foreach ($this->_items as $item) {
        $items_html[] = $item->__toString();
      }

      $items_html[] = "</ul>";
    }

    if (!empty($this->_text)) {
      $items_html[] = "</li>";
    }
    return implode("\n", $items_html);
  }
}
