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
class Rearrange_Controller extends Controller {

  public function _show($id) {
    Kohana::log("debug", sprintf("[%s%s] Rearrange::_show(): %s", __FILE__, __LINE__, print_r($id, true)));
    print rearrange::get_children($id)->render();
  }

  public function _index() {
    Kohana::log("debug", sprintf("[%s%s] Rearrange::_index(): %s", __FILE__, __LINE__, print_r(1, true)));
    print rearrange::get_children()->render();
  }

  /**
   * Handle dispatching for all REST controllers.
   */
  public function __call($function, $args) {
    Kohana::log("debug", sprintf("[%s%s] Rearrange::$function(): %s", __FILE__, __LINE__, print_r($args, true)));
    // If no parameter was provided after the controller name (eg "/albums") then $function will
    // be set to "index".  Otherwise, $function is the first parameter, and $args are all
    // subsequent parameters.
    $request_method = rest::request_method();
    if ($function == "index" && $request_method == "get") {
      return $this->_index();
    }

    // @todo this needs security checks
    $id = $function;

    switch ($request_method) {
    case "get":
      $this->_show($id);

      if (Session::instance()->get("use_profiler", false)) {
        $profiler = new Profiler();
        $profiler->render();
      }
      return;

    case "put":
      return $this->_update($id);

    case "delete":
      return $this->_delete($id);

    case "post":
      return $this->_create($id);
    }
      }
}