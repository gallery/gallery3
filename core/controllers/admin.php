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
class Admin_Controller extends Controller {
  private $theme;

  public function __construct($theme=null) {
    if (!(user::active()->admin)) {
      throw new Exception("@todo UNAUTHORIZED", 401);
    }
    parent::__construct();
  }

  public function __call($controller_name, $args) {
    if (request::method() == "post") {
      access::verify_csrf();
    }

    if ($controller_name == "index") {
      $controller_name = "dashboard";
    }
    $controller_name = "Admin_{$controller_name}_Controller";

    if ($args) {
      $method = array_shift($args);
    } else {
      $method = "index";
    }

    if (!method_exists($controller_name, $method)) {
      return kohana::show_404();
    }

    call_user_func_array(array(new $controller_name, $method), $args);
  }
}

