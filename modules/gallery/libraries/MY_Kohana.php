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
   * cause problems with Kohana 2.4 if code obfuscation is set to level 3+.
   *
   * The problem is this: if you're searching for a method in a class that does not exist,
   * Zend Guard Loader may continue to try and load the class, eventually leading to a seg fault.
   *
   * Instead, if the class isn't found and we can see that it was method_exists that searched for
   * it (as opposed to class_exists or interface_exists), return a dummy class. In the general case,
   * this is not foolproof: if you run method_exists on a nonexistent class, then later run
   * class_exists on the same class, you'll get a false positive.  However, this case doesn't seem
   * to affect Gallery, so it's a sufficient workaround.
   *
   * Ref on basic problem: http://forums.zend.com/viewtopic.php?f=57&t=42383  (English)
   * Ref on partial patch: http://forums.zend.com/viewtopic.php?f=57&p=165438 (English)
   * Ref on partial patch: http://blog.teatime.com.tw/1/post/403              (Chinese)
   */
  public static function auto_load($class) {
    static $apply_patch = null;
    if (is_null($apply_patch)) {
      // Set to true if code obfuscation is at level 3+, false otherwise.
      $apply_patch = (function_exists("zend_current_obfuscation_level") &&
        (zend_current_obfuscation_level() >= 3));
    }

    $found = parent::auto_load($class);

    if ($apply_patch && !$found) {
      $stack = debug_backtrace();
      if ($stack[2]["function"] == "method_exists") {
        // Load a dummy class.  Since it's empty, the method_exists will still return false,
        // but the class itself will now exist.
        eval("class $class {}");
      }
    }

    // Return the same result.
    return $found;
  }
}
