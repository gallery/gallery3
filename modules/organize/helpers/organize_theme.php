<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
    $item = $theme->item();
    if ($item && access::can("edit", $item) && $item->is_album()) {
      // @todo: Defer loading js/css until we're loading the organize dialog as <script> and
      // <link> elements so that we're not forcing them to be downloaded on every page view (which
      // is expensive in terms of browser latency).  When we do that, we'll have to figure out an
      // approach that lets us continue to use the Kohana cascading filesystem.
      $theme->script("organize.js");
      $theme->css("organize.css");
    }
  }
}
