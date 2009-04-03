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
class organize_theme {
  static function head($theme) {
    // @tdo remove the addition css and organize.js (just here to test)
    $script[] = html::script("modules/organize/js/organize_init.js");
    $script[] = html::script("modules/organize/js/organize.js");
    $script[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"" .
      url::file("modules/organize/css/organize.css") . "\" />";
    return implode("\n", $script);
    //return html::script("modules/organize/js/organize_init.js");
  }
}
