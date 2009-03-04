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
class server_add_theme_Core {
  static function admin_head($theme) {
    $head = array();
    if (Router::$current_uri == "admin/server_add") {
      $head[] = "<link media=\"screen, projection\" rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file("lib/jquery.autocomplete.css") . "\" />";
      $head[] = "<link media=\"screen, projection\" rel=\"stylesheet\" type=\"text/css\" href=\"" .
        url::file("modules/server_add/css/admin.css") . "\" />";
      $base = url::base(true);
      $csrf = access::csrf_token();
      $head[] = "<script> var base_url = \"$base\"; var csrf = \"$csrf\";</script>";

      $head[] = html::script("lib/jquery.autocomplete.pack.js");
      $head[] = html::script("modules/server_add/js/admin.js");
    }
    
    return implode("\n", $head);
  }
}