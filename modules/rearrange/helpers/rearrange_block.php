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
class rearrange_block_Core {
  public static function head($theme) {
    $head[] = html::script("modules/rearrange/js/jquery-ui-core-draggable-droppable-1.5.2.js");
    $head[] = html::script("modules/rearrange/js/jquery.gallery.rearrange.tree.js");

    $url = url::file("modules/rearrange/css/rearrange.css");
    $head[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$url\" " .
      "media=\"screen,print,projection\" />";

    return implode("\n", $head);
  }
}
