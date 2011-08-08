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

abstract class Display_Context_Core {
  private $_display_context_name;
  private $_data;

  static function factory($display_context_name=null) {
    if (empty($display_context_name)) {
      $context = Session::instance()->get("display_context", new Item_Display_Context());
      $context = unserialize($context);
    } else {
      $class_prefix = ucfirst(strtolower($display_context_name));
      $class_name = "{$class_prefix}_Display_Context";
      $context = new $class_name();
    }

    return $context;
  }

  protected function __construct($display_context_name) {
    // $this->reset($display_context_name);
    $this->_data = array();
    $this->_display_context_name = $display_context_name;
  }

  final function get($key) {
    return empty($this->_data[$key]) ? null : $this->_data[$key];
  }

  final function set($key, $value=null) {
    if (is_array($key)) {
      if ((array)$key == $key) {
        $this->_data = array_merge($this->_data, $key);
      } else {
        $this->_data = array_merge($this->_data, array_fill_keys($key, $value));
      }
    } else {
      $this->_data[$key] = $value;
    }
    return $this;
  }

  final protected function dynamic_item($title, $url) {
    $dynamicItem = new Dynamic_Item();
    $dynamicItem->title = $title;
    $dynamicItem->url = $url;
    return $dynamicItem;
  }

  final function save() {
    Session::instance()->set("display_context", serialize($this));
    return $this;
  }

  abstract function display_context($item);
  abstract function bread_crumb($item);
}

class Dynamic_Item {
  var $url;
  var $title;
  var $id = 0;

  public function url($query=null) {
    if ($query) {
      $this->url .= "?$query";
    }
    return url::site($this->url);
  }
}


