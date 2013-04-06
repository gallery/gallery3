<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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
class Gallery_Purifier {
  private static $_purifier = null;

  static function purify($dirty_html) {
    if (!isset(self::$_purifier)) {
      require(MODPATH . "gallery/vendor/htmlpurifier/HTMLPurifier.standalone.php");
      $config = HTMLPurifier_Config::createDefault();
      foreach (Kohana::$config->load("purifier") as $category => $key_value) {
        foreach ($key_value as $key => $value) {
          $config->set("$category.$key", $value);
        }
      }
      self::$_purifier = new HTMLPurifier($config);
    }

    return self::$_purifier->purify($dirty_html);
  }
}
