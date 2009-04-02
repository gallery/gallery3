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

/**
 * Scans all source code for messages that need to be localized.
 */
class l10n_scanner_Core {
  // Based on Drupal's potx module, originally written by:
  // Gbor Hojtsy http://drupal.org/user/4166
  public static $cache;

  static function process_message($message, &$cache) {
    if (empty($cache)) {
      foreach (Database::instance()
               ->select("key")
               ->from("incoming_translations")
               ->where("locale", "root")
               ->get() as $row) {
        $cache[$row->key] = true;
      }
    }

    $key = I18n::get_message_key($message);
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }

    $entry = ORM::factory("incoming_translation", array("key" => $key));
    if (!$entry->loaded) {
      $entry->key = $key;
      $entry->message = serialize($message);
      $entry->locale = "root";
      $entry->save();
    }
  }

  static function scan_php_file($file, &$cache) {
    $code = file_get_contents($file);
    $raw_tokens = token_get_all($code);
    unset($code);

    $tokens = array();
    $func_token_list = array("t" => array(), "t2" => array());
    $token_number = 0;
    // Filter out HTML / whitespace, and build a lookup for global function calls.
    foreach ($raw_tokens as $token) {
      if ((!is_array($token)) || (($token[0] != T_WHITESPACE) && ($token[0] != T_INLINE_HTML))) {
        if (is_array($token)) {
          if ($token[0] == T_STRING && in_array($token[1], array("t", "t2"))) {
            $func_token_list[$token[1]][] = $token_number;
          }
        }
        $tokens[] = $token;
        $token_number++;
      }
    }
    unset($raw_tokens);

    if (!empty($func_token_list["t"])) {
      l10n_scanner::_parse_t_calls($tokens, $func_token_list["t"], $cache);
    }
    if (!empty($func_token_list["t2"])) {
      l10n_scanner::_parse_plural_calls($tokens, $func_token_list["t2"], $cache);
    }
  }

  static function scan_info_file($file, &$cache) {
    $code = file_get_contents($file);
    if (preg_match("#name\s*?=\s*(.*?)\ndescription\s*?=\s*(.*)\n#", $code, $matches)) {
      unset($matches[0]);
      foreach ($matches as $string) {
        l10n_scanner::process_message($string, $cache);
      }
    }
  }

  private static function _parse_t_calls(&$tokens, &$call_list, &$cache) {
    foreach ($call_list as $index) {
      $function_name = $tokens[$index++];
      $parens = $tokens[$index++];
      $first_param = $tokens[$index++];
      $next_token = $tokens[$index];

      if ($parens == "(") {
        if (in_array($next_token, array(")", ","))
            && (is_array($first_param) && ($first_param[0] == T_CONSTANT_ENCAPSED_STRING))) {
          $message = self::_escape_quoted_string($first_param[1]);
          l10n_scanner::process_message($message, $cache);
        } else {
          // t() found, but inside is something which is not a string literal.
          // @todo Call status callback with error filename/line.
        }
      }
    }
  }

  private static function _parse_plural_calls(&$tokens, &$call_list, &$cache) {
    foreach ($call_list as $index) {
      $function_name = $tokens[$index++];
      $parens = $tokens[$index++];
      $first_param = $tokens[$index++];
      $first_separator = $tokens[$index++];
      $second_param = $tokens[$index++];
      $next_token = $tokens[$index];

      if ($parens == "(") {
        if ($first_separator == "," && $next_token == ","
            && is_array($first_param) && $first_param[0] == T_CONSTANT_ENCAPSED_STRING
            && is_array($second_param) && $second_param[0] == T_CONSTANT_ENCAPSED_STRING) {
          $singular = self::_escape_quoted_string($first_param[1]);
          $plural = self::_escape_quoted_string($first_param[1]);
          l10n_scanner::process_message(array("one" => $singular, "other" => $plural), $cache);
        } else {
          // t2() found, but inside is something which is not a string literal.
          // @todo Call status callback with error filename/line.
        }
      }
    }
  }

  /**
   * Escape quotes in a strings depending on the surrounding
   * quote type used.
   *
   * @param $str The strings to escape
   */
  private static function _escape_quoted_string($str) {
    $quo = substr($str, 0, 1);
    $str = substr($str, 1, -1);
    if ($quo == '"') {
      $str = stripcslashes($str);
    } else {
      $str = strtr($str, array("\\'" => "'", "\\\\" => "\\"));
    }
    return addcslashes($str, "\0..\37\\\"");
  }
}
