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
class media_rss_block_Core {
  public static function head($theme) {
    if ($theme->item()) {
      if ($theme->item()->type == "album") {
        $url = url::site("media_rss/albums/{$theme->item()->id}");
      } else {
        $url = url::site("media_rss/albums/{$theme->item()->parent_id}");
      }
    } else if ($theme->tag()) {
      $url = url::site("media_rss/tags/{$theme->tag()->id}");
    }

    if (!empty($url)) {
      return "<link rel=\"alternate\" type=\"" . rest::RSS . "\" href=\"$url\" />";
    }
  }
}
