<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class info_theme_Core {
  static function thumb_info($theme, $item) {
    $results = "";
    if ($item->view_count) {
      $results .= "<li>";
      $results .= t("Views: %view_count", array("view_count" => $item->view_count));
      $results .= "</li>";
    }
    if ($item->owner) {
      $results .= "<li>";
      if ($item->owner->url) {
        $results .= t("By: <a href=\"%owner_url\">%owner_name</a>",
                      array("owner_name" => $item->owner->display_name(),
                            "owner_url" => $item->owner->url));
      } else {
        $results .= t("By: %owner_name", array("owner_name" => $item->owner->display_name()));
      }
      $results .= "</li>";
    }
    return $results;
  }
}