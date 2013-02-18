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
final class Kohana extends Kohana_Core {
  /**
   * Wrapper function for Kohana::auto_load that provides compatibility with Zend Guard Loader's
   * code obfuscation.  Zend Guard is enabled by default on many PHP 5.3+ installations and can
   * cause problems with Kohana 2.4.  When a class is not found, Zend Guard Loader may continue to
   * try and load the class, eventually leading to a seg fault.
   *
   * Instead, if we can't find the class and we can see that code obfuscation is at level 3+, let's
   * load a dummy class.  This does not change the return value, so Kohana still knows that
   * there is no class.
   *
   * This is based on the patch described here: http://blog.teatime.com.tw/1/post/403
   */
  public static function auto_load($class) {
    $found = parent::auto_load($class);

    if (!$found && function_exists("zend_current_obfuscation_level") &&
        (zend_current_obfuscation_level() >= 3)) {
      // Load a dummy class instead.
      eval("class $class {}");
    }

    // Return the same result.
    return $found;
  }
}