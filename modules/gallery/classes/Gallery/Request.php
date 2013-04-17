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
class Gallery_Request extends Kohana_Request {
  protected $_args;

  /**
   * Retrieves a value from the route args.  This uses a syntax similar to param().
   *
   *   $args = $request->arg();          // Returns all args
   *   $id   = $request->arg(0);         // Returns 0th arg
   *   $type = $request->arg(1, "item"); // Returns 1st arg, defaults to "item" if not set
   *
   * @param   mixed  $key      Key of the value (string or int)
   * @param   mixed  $default  Default value if the key is not set (optional)
   * @return  mixed
   */
  public function arg($key=null, $default=null) {
    if (!isset($this->_args)) {
      $this->_args = preg_replace("|/+|", "/", trim($this->param("args"), "/"));
      $this->_args = (array) explode("/", $this->_args);
      $this->_args = Purifier::clean_html($this->_args);
    }

    return isset($key) ? Arr::get($this->_args, $key, $default) : $this->_args;
  }
}
