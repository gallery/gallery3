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

class Dynamic_Item {
  var $url;
  var $title;
  var $id = 0;

  public function url($query=null) {
    if ($query) {
      $this->url .= "?$query";
    }
    return $this->url;
  }
}

class Photo_Display_Context_Core {
  static $context = null;

  private $_callback;
  private $_data;

  static function factory() {
    self::$context = Session::instance()->get("photoContext", null);

    if (empty(self::$context)) {
      self::$context = new Photo_Display_Context();
    } else {
      self::$context = unserialize(self::$context);
     }

    return self::$context;
  }

  function get_context($item) {
    if (empty($this->_callback)) {
      // safety net for backwards compatibility
      $this->_callback = "item::get_context";
    }
    return call_user_func($this->_callback, $item, $this);
  }

  // @param $item
  function set_context_callback($callback) {
    $this->_callback = $callback;
    return $this;
  }

  function set_data($data) {
    $this->_data = $data;
    return $this;
  }

  function data() {
    return $this->_data;
  }

  function dynamic_item($title, $url) {
    $dynamicItem = new Dynamic_Item();
    $dynamicItem->title = $title;
    $dynamicItem->url = $url;
    return $dynamicItem;
  }

  function save() {
    Session::instance()->set("photoContext", serialize(self::$context));
    return $this;
  }
}
