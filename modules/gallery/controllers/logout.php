<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Logout_Controller extends Controller {
  public function index() {
    access::verify_csrf();
    auth::logout();
    if ($continue_url = Input::instance()->get("continue")) {
      $components = explode("/", parse_url($continue_url, PHP_URL_PATH), 4);
      $item = url::get_item_from_uri($components[3]);
      if (access::can("view", $item)) {
        // Don't use url::redirect() because it'll call url::site() and munge the continue url.
        header("Location: {$item->relative_url()}");
      } else {
        url::redirect(item::root()->abs_url());
      }
    } else {
        url::redirect(item::root()->abs_url());
    }
  }
}