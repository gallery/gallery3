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
class L10n_Scanner_Core {
  // Based on Drupal's potx module, originally written by:
  // Gbor Hojtsy http://drupal.org/user/4166

  private static $_instance;

  private $_index_keys;

  private function __construct() {}

  static function instance() {
    if (self::$_instance == null) {
      self::$_instance = new L10n_Scanner_Core();
    }

    return self::$_instance;
  }

  // TODO(andy_st): Report progress via callback
  function update_index() {
    // Load the current index into memory
    $this->_index_keys = array();
    foreach (Database::instance()
             ->select("key")
             ->from("incoming_translations")
             ->where(array("locale" => "root"))
             ->get()
             ->as_array() as $row) {
      $this->_index_keys[$row->key] = true;
    }

    // Index all files
    $dir = new L10n_Scanner_File_Filter_Iterator(
      new RecursiveIteratorIterator(
        new L10n_Scanner_Directory_Filter_Iterator(
          new RecursiveDirectoryIterator(DOCROOT))));
    foreach ($dir as $file) {
      if (substr(strrchr($file->getFilename(), '.'), 1) == "php") {
        $this->_scan_php_file($file, $this);
      } else {
        $this->_scan_info_file($file, $this);
      }
    }
  }

  function process_message($message) {
    $key = I18n::get_message_key($message);
    if (!isset($this->_index_keys[$key])) {
      $entry = ORM::factory("incoming_translation");
      $entry->key = $key;
      $entry->message = serialize($message);
      $entry->locale = "root";
      $entry->save();

      $this->_index_keys[$key] = true;
    }
  }

  private function _scan_php_file($file, &$message_handler) {
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
      $this->_parse_t_calls($tokens, $func_token_list["t"], $message_handler);
    }
    if (!empty($func_token_list["t2"])) {
      $this->_parse_plural_calls($tokens, $func_token_list["t2"], $message_handler);
    }
  }

  private function _scan_info_file($file, &$message_handler) {
    $code = file_get_contents($file);
    if (preg_match("#name\s*?=\s*(.*?)\ndescription\s*?=\s*(.*)\n#", $code, $matches)) {
      unset($matches[0]);
      foreach ($matches as $string) {
        $message_handler->process_message($string);
      }
    }
  }

  private function _parse_t_calls(&$tokens, &$call_list, &$message_handler) {
    foreach ($call_list as $index) {
      $function_name = $tokens[$index++];
      $parens = $tokens[$index++];
      $first_param = $tokens[$index++];
      $next_token = $tokens[$index];

      if ($parens == "(") {
        if (in_array($next_token, array(")", ","))
            && (is_array($first_param) && ($first_param[0] == T_CONSTANT_ENCAPSED_STRING))) {
          $message = self::_escape_quoted_string($first_param[1]);
          $message_handler->process_message($message);
        } else {
          // t() found, but inside is something which is not a string literal.
          // TODO(andy_st): Call status callback with error filename/line.
        }
      }
    }
  }

  private function _parse_plural_calls(&$tokens, &$call_list, &$message_handler) {
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
          $message_handler->process_message(array("one" => $singular, "other" => $plural));
        } else {
          // t2() found, but inside is something which is not a string literal.
          // TODO(andy_st): Call status callback with error filename/line.
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

class L10n_Scanner_Directory_Filter_Iterator extends RecursiveFilterIterator {
  function accept() {
    if ($this->getInnerIterator()->isFile()) {
      return true;
    }

    // Skip anything that doesn't need to be localized.
    $folder = $this->getInnerIterator()->getFilename();
    $path_name = $this->getInnerIterator()->getPathName();
    return !(
      $folder == ".svn" ||
      $folder == "tests" ||
      strpos($path_name, DOCROOT . "var") !== false ||
      strpos($path_name . "/", SYSPATH) !== false);
  }
}

class L10n_Scanner_File_Filter_Iterator extends FilterIterator {
  function accept() {
    // Skip anything that doesn't need to be localized.
    $filename = $this->getInnerIterator()->getFilename();
    return in_array(substr(strrchr($filename, '.'), 1), array("php", "info"));
  }
}
