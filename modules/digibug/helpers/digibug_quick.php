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
class digibug_quick_Core {
  static function buttons($item, $page_type) {
    $elements["center"] = array();
    if (access::can("view", $item) && $item->type == "photo") {
      $csrf = access::csrf_token();
      $elements["center"][] = (object)array(
        "title" => t("Print photo with Digibug"),
        "class" => "gButtonLink",
        "icon" => "ui-icon-print",
        "href" => url::site("digibug/print/$item->id?csrf=$csrf"));
    }

    return $elements;
  }
}
