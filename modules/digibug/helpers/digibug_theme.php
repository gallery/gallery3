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
class digibug_theme_Core {
  static function head($theme) {
    $head[] = "<link media=\"screen, projection\" rel=\"stylesheet\" type=\"text/css\" href=\"" .
      url::file("modules/digibug/css/digibug.css") . "\" />";
    $head[] = html::script("modules/digibug/js/digibug.js");;
    return implode("\n", $head);
  }

  static function admin_head($theme) {
    return "<link media=\"screen, projection\" rel=\"stylesheet\" type=\"text/css\" href=\"" .
      url::file("modules/digibug/css/digibug.css") . "\" />";
  }

  static function thumb_bottom($theme, $child) {
    if ($theme->page_type() == "album" && $child->type == "photo") {
      $v = new View("digibug_album.html");
      $v->id = $child->id;
      $v->return = "album/{$child->parent()->id}";
      $v->title = t("Print photo with Digibug");
      return $v->render();
    }
    return "";
  }
}
