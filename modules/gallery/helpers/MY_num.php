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
class num extends num_Core {
  /**
   * Convert a size value as accepted by PHP's shorthand to bytes.
   * ref: http://us2.php.net/manual/en/function.ini-get.php
   * ref: http://us2.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
   */
  static function convert_to_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
    case 'g':
      $val *= 1024;
    case 'm':
      $val *= 1024;
    case 'k':
      $val *= 1024;
    }

    return $val;
  }

  /**
   * Convert a size value as accepted by PHP's shorthand to bytes.
   * ref: http://us2.php.net/manual/en/function.ini-get.php
   * ref: http://us2.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
   */
  static function convert_to_human_readable($num) {
    foreach (array("G" => 1e9, "M" => 1e6, "K" => 1e3) as $k => $v) {
      if ($num > $v) {
        $num = round($num / $v) . $k;
      }
    }
    return $num;
  }
}
