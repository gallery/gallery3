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
abstract class Menu_Item {
  protected $text;
  protected $type;
  protected $url;

  protected function __construct($type, $text, $url) {
    $this->text = $text;
    $this->type = $type;
    $this->url = $url;
  }
}

class Menu_Link extends Menu_Item {
  public function __construct($text="", $url="#") {
    parent::__construct("link", $text, $url);
  }

  public function __toString() {
    return "<li><a href=\"$this->url\">$this->text</a><li>";
  }
}

class Menu_Dialog extends Menu_Item {
  public function __construct($text="", $url="#", $class="gDialogLink") {
    parent::__construct("dialog", $text, $url);
    $this->dialog_class = $class;
    $this->title = $text;
  }


  public function __toString() {
    return "<li><a class=\"$this->dialog_class\" href=\"$this->url\" " .
           "title=\"$this->title\">$this->text</a></li>";
  }
}

class Menu_Core extends Menu_Item {
  public function __construct($text="", $url="#") {
    parent::__construct("menu", $text, $url);
    $this->_data['items'] = array();
  }

  public function append($menu_item) {
    $items = $this->items;
    $items[] = $menu_item;
    $this->items = $items;
  }

  public function get($text) {
    foreach ($this->items as $item) {
      if ($item->text == $text) {
        return $item;
      }
    }
    return false;
  }

  private function _get_index($text) {
    foreach ($this->items as $idx => $item) {
      if ($item->text == $text) {
        return (int)$idx;
      }
    }
    return false;
  }

  public function insert_before($text, $menu_item) {
    $offset = $this->_get_index($text);
    Kohana::log("debug", "$offset: $offset");

    $items = $this->items;

    $front_part = ($offset == 0) ? array() : array_splice($items, 0, $offset);
    $back_part = ($offset == 0) ? $this->items : array_splice($items, $offset - 1);
    Kohana::log("debug", print_r($front_part, 1));
    Kohana::log("debug", print_r($front_part, 1));
    $this->items = array_merge($front_part, array($menu_item), $back_part);
  }

  public function insert_after($text, $menu_item) {
    $offset = $this->_get_index($text);
    $last_offset = count($this->items) - 1;
    // If not found, then append to the end
    if ($offset == false) {
      $offset = $last_offset;
    }

    $items = $this->items;

    $front_part = ($offset == $last_offset) ? $this->items : array_splice($items, 0, $offset + 1);
    $back_part = ($offset == $last_offset) ? array() : array_splice($items,  $offset - 1);
    $this->items = array_merge($front_part, array($menu_item), $back_part);
  }

  public function __toString() {
    Kohana::log("debug", print_r($this, 1));
    $items_html = array();
    $item_text = $this->text;
    if (!empty($item_text)) {
      $items_html[] = "<li><a href=\"#\">$item_text</a>";
    }

    $items = $this->items;
    if (!empty($items)) {
      $items_html[] = "<ul>";

      foreach ($items as $item) {
        $items_html[] = $item->__toString();
      }

      $items_html[] = "</ul>";
    }

    if (!empty($item_text)) {
      $items_html[] = "</li>";
    }
    return implode("\n", $items_html);
  }
}
