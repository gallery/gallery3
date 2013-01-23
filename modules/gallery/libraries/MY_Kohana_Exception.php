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
class Kohana_Exception extends Kohana_Exception_Core {
  /**
   * Dump out the full stack trace as part of the text representation of the exception.
   */
  public static function text($e) {
    if ($e instanceof Kohana_404_Exception) {
      return "File not found: " . rawurlencode(Router::$complete_uri);
    } else {
      return sprintf(
        "%s [ %s ]: %s\n%s [ %s ]\n%s",
        get_class($e), $e->getCode(), strip_tags($e->getMessage()),
        $e->getFile(), $e->getLine(),
        $e->getTraceAsString());
    }
  }

  /**
   * @see Kohana_Exception::dump()
   */
  public static function dump($value, $length=128, $max_level=5) {
    return self::safe_dump($value, null, $length, $max_level);
  }

  /**
   * A safer version of dump(), eliding sensitive information in the dumped
   * data, such as session ids and passwords / hashes.
   */
  public static function safe_dump($value, $key, $length=128, $max_level=5) {
    return parent::dump(self::_sanitize_for_dump($value, $key, $max_level), $length, $max_level);
  }

  /**
   * Elides sensitive data which shouldn't be echoed to the client,
   * such as passwords, and other secrets.
   */
  /* Visible for testing*/ static function _sanitize_for_dump($value, $key=null, $max_level) {
    // Better elide too much than letting something through.
    // Note: unanchored match is intended.
    if (!$max_level) {
      // Too much recursion; give up.  We gave it our best shot.
      return $value;
    }

    $sensitive_info_pattern =
      '/(password|pass|email|hash|private_key|session_id|session|g3sid|csrf|secret)/i';
    if (preg_match($sensitive_info_pattern, $key) ||
        (is_string($value) && preg_match('/[a-f0-9]{20,}/i', $value))) {
      return 'removed for display';
    } else if (is_object($value)) {
      if ($value instanceof Database) {
        // Elide database password, host, name, user, etc.
        return get_class($value) . ' object - details omitted for display';
      } else if ($value instanceof User_Model) {
        return get_class($value) . ' object for "' . $value->name . '" - details omitted for display';
      }
      return self::_sanitize_for_dump((array) $value, $key, $max_level - 1);
    } else if (is_array($value)) {
      $result = array();
      foreach ($value as $k => $v) {
        $actual_key = $k;
        $key_for_display = $k;
        if ($k[0] === "\x00") {
          // Remove the access level from the variable name
          $actual_key = substr($k, strrpos($k, "\x00") + 1);
          $access = $k[1] === '*' ? 'protected' : 'private';
          $key_for_display = "$access: $actual_key";
        }
        if (is_object($v)) {
          $key_for_display .= ' (type: ' . get_class($v) . ')';
        }
        $result[$key_for_display] = self::_sanitize_for_dump($v, $actual_key, $max_level - 1);
      }
    } else {
      $result = $value;
    }
    return $result;
  }

  public static function debug_path($file) {
    return html::clean(parent::debug_path($file));
  }
}