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
class Gallery_Inflector extends Kohana_Inflector {
  /**
   * Makes a phrase camel case. Spaces and underscores will be removed.
   *
   * We extend Kohana's implementation by adding a second argument which specifies
   * whether or not to capitalize the first letter, too.  This is useful when converting
   * between module names and their corresponding classes ("foo_bar" --> "FooBar").
   *
   *     $str = Inflector::camelize('mother cat');     // "motherCat"
   *     $str = Inflector::camelize('kittens in bed'); // "kittensInBed"
   *     $str = Inflector::camelize('foo_bar');        // "fooBar"
   *     $str = Inflector::camelize('foo_bar', true);  // "FooBar"
   *
   * @param   string  $str      phrase to camelize
   * @param   boolean $ucfirst  flag to capitalize the first letter as well (default: false)
   * @return  string
   */
  public static function camelize($str, $ucfirst=false) {
    if ($ucfirst) {
      // This is strongly based on See Kohana_Inflector::camelize() and should give the same result
      // as ucfirst(Inflector::camelize($str)), but is more direct.  This is because Kohana
      // prepends a dummy character, runs ucwords, then removes the (now capitalized) dummy
      // character to get the first letter lowercase.  We simply don't add that dummy character.
      $str = strtolower(trim($str));
      $str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
      return str_replace(' ', '', $str);
    } else {
      // Same as default Kohana case.
      return parent::camelize($str);
    }
  }
}
