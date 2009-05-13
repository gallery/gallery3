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
class slideshow_event_Core {
  static function module_change($changes) {
    if (!module::is_installed("rss") || in_array("rss", $changes->uninstall)) {
      site_status::warning(
        t("The Slideshow module requires the RSS module.  " .
          "<a href=\"%url\">Install the RSS module now</a>",
          array("url" => url::site("admin/modules"))),
        "slideshow_needs_rss");
    } else {
      site_status::clear("slideshow_needs_rss");
    }
  }
}
