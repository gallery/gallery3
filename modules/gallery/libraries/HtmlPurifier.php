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
class HtmlPurifier_Core {
  private static $_instance;

  static function instance($config=null) {
    require_once(dirname(__file__) . "/HTMLPurifier/HTMLPurifier.auto.php");
    if (self::$_instance == NULL) {
      $config = isset($config) ? $config : Kohana::config('purifier');
      $purifier_config = HTMLPurifier_Config::createDefault();
      foreach ($config as $category => $key_value) {
        foreach ($key_value as $key => $value) {
          $purifier_config->set("$category.$key", $value);
        }
      }
      self::$_instance = new HtmlPurifier($purifier_config);
    }

    return self::$_instance;
  }
}
