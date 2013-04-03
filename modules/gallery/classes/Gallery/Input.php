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
class Gallery_Input {
  // The four different levels of cleaning we can do, in order from least to most restrictive.
  const RAW = -273;     // no cleaning                        (use *VERY* sparingly and cautiously!)
  const PURIFIER = 0;   // use HTMLPurifier to combat XSS     (default)
  const WORD = 10;      // allow [0-9a-zA-Z:_.-], make rest _ (quick, useful for key names)
  const HEX = 16;       // allow [0-9a-f], remove rest        (quick, useful for hashes like csrf)

  // Initialize $inputs array.
  private static $inputs = array();

  /**
   * Clean a string.  This can be used independently of the rest of the Input class.
   *
   * @param   $dirty  string  dirty value
   * @param   $level  int     cleaning level constant (default is Input::PURIFIER)
   * @return          string  cleaned value
   */
  public static function clean($dirty, $level=0) {
    switch ($level) {
      case Input::RAW:
        return $dirty;
      case Input::PURIFIER:
        return Purifier::purify($dirty);
      case Input::WORD:
        return preg_replace("/[^0-9a-zA-Z:_.-]/", "_", $dirty);
      case Input::HEX:
        return preg_replace("/[^0-9a-f]/", "", $dirty);
      default:
        return null;
    }
  }

  /**
   * Clean and return a value in $_GET, or null if not found.
   * This also cleans all key names using Input::WORD.
   *
   * @param   $key    string
   * @param   $level  int     cleaning level constant (default is Input::PURIFIER)
   * @return          string  cleaned value (or null if not found)
   */
  public static function get($key, $level=0) {
    if (!isset(self::$inputs["get"])) {
      // Initialize GET array
      self::$inputs["get"] = array();
      foreach ($_GET as $raw_key => $raw_value) {
        self::$inputs["get"][self::clean($raw_key, self::WORD)][self::RAW] = $raw_value;
      }
    }

    return self::_process("get", $key, $level);
  }

  /**
   * Clean and return a value in $_POST, or null if not found.
   * This also cleans all key names using Input::WORD.
   *
   * @param   $key    string
   * @param   $level  int     cleaning level constant (default is Input::PURIFIER)
   * @return          string  cleaned value (or null if not found)
   */
  public static function post($key, $level=0) {
    if (!isset(self::$inputs["post"])) {
      // Initialize POST array
      self::$inputs["post"] = array();
      foreach ($_POST as $raw_key => $raw_value) {
        self::$inputs["post"][self::clean($raw_key, self::WORD)][self::RAW] = $raw_value;
      }
    }

    return self::_process("post", $key, $level);
  }

  /**
   * Clean and return a value in $_COOKIE, or null if not found or not signed correctly.
   * This also cleans all key names using Input::WORD (except RFC2109-compliant special attributes).
   *
   * @param   $key    string
   * @param   $level  int     cleaning level constant (default is Input::PURIFIER)
   * @return          string  cleaned value (or null if not found)
   */
  public static function cookie($key, $level=0) {
    if (!isset(self::$inputs["cookie"])) {
      // Initialize COOKIE array
      self::$inputs["cookie"] = array();
      foreach ($_COOKIE as $raw_key => $raw_value) {
        // Use Cookie::get() to handle signed cookies.  Also, skip key name
        // cleaning for RFC2109-compliant special attributes
        if (in_array($raw_key, array('$Version', '$Path', '$Domain'))) {
          self::$inputs["cookie"][$raw_key][self::RAW] = Cookie::get($raw_key);
        } else {
          self::$inputs["cookie"][self::clean($raw_key, self::WORD)][self::RAW] = Cookie::get($raw_key);
        }
      }
    }

    return self::_process("cookie", $key, $level);
  }

  /**
   * Clean and return a value in $_SERVER, or null if not found.
   * Unlike the other functions, this does not clean the key names.
   *
   * @param   $key    string
   * @param   $level  int     cleaning level constant (default is Input::PURIFIER)
   * @return          string  cleaned value (or null if not found)
   */
  public static function server($key, $level=0) {
    if (!isset(self::$inputs["server"])) {
      // Initialize SERVER array
      self::$inputs["server"] = array();
      foreach ($_SERVER as $raw_key => $raw_value) {
        // We can trust the $_SERVER key names - don't clean them.
        self::$inputs["server"][$raw_key][self::RAW] = $raw_value;
      }
    }

    return self::_process("server", $key, $level);
  }

  /**
   * Clean, cache, and return values in the $inputs array.  This requires that $inputs[$name]
   * already be initialized and filled with its raw values.
   */
  private static function _process($name, $key, $level) {
    if (isset(self::$inputs[$name][$key])) {
      if (!isset(self::$inputs[$name][$key][$level]) {
        // Not yet cleaned but it does exist - clean it.
        self::$inputs[$name][$key][$level] =
          self::clean(self::$inputs[$name][$key][Input::RAW], $level);
      }
      return self::$inputs[$name][$key][$level];
    }
    // Key not found - return null.
    return null;
  }
}
