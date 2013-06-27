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
class Controller_Auth_Test extends Unittest_TestCase {
  public function test_find_missing_auth() {
    $git_ignores = explode("\n", `git ls-files -o -i --exclude-standard`);

    $file_types = array(
      "controller_admin" => array(
        "glob_filter"   => "*/*/classes/*/Controller/Admin/*.php",
        "class_extends" => array("Controller_Admin")),
      "controller" => array(
        "glob_filter"   => "*/*/classes/*/Controller/*.php",
        "class_extends" => array("Controller_Admin", "Controller")),
      "rest" => array(
        "glob_filter"   => "*/*/classes/*/Rest/*.php",
        "class_extends" => array("Rest")),
      "rss" => array(
        "glob_filter"   => "*/*/classes/*/Hook/*Rss.php",
        "class_extends" => array()));

    $needle_types = array(
      "uses_input" => array(
        "Request::current()->query(",
        "Request::current()->post(",
        "Request::current()->cookie(",
        "Request::initial()->query(",
        "Request::initial()->post(",
        "Request::initial()->cookie(",
        '$this->request->query(',
        '$this->request->post(',
        '$this->request->cookie(',
        "RAW::",
        "Formo::"),
      "checks_authorization" => array(
        "Access::required(",
        "HTTP_Exception::factory(403",
        "Rest_Exception::factory(403"),
      "checks_csrf" => array(
        "Access::verify_csrf(",
        "->load()->validate()"));

    // Process the filters and build the list of files to check.
    $files = array();
    foreach ($file_types as $type => $data) {
      foreach (array_diff(glob($data["glob_filter"]), $git_ignores) as $file) {
        $files[$file] = $type;
      }
    }

    // Loop through each file and build the list of found functions.
    $found = array();
    foreach ($files as $file => $type) {
      // Skip unittest or empty files (last line in each list from git ls-files is empty)
      if (preg_match("{modules/(gallery_)?unittest/}", $file) || !$file) {
        continue;
      }

      // Get list of all tokens without whitespace, simplifying parsing.
      $tokens = array();
      foreach (token_get_all(file_get_contents($file)) as $token) {
        if (!is_array($token) || $token[0] != T_WHITESPACE) {
          $tokens[] = $token;
        }
      }

      // Check each token.
      $open_braces = 0;
      $function = null;
      $class_extends = null;
      for ($token_i = 0; $token_i < count($tokens); $token_i++) {
        // 0 open braces = outside class context - search for "extends"
        // 1 open brace  = in class context - search for "function"
        // 2 open braces = in function context - search for needles (defined above)
        if (static::token_matches("}", $tokens, $token_i)) {
          // Found "}"
          $open_braces--;
          if ($open_braces == 1 && $function) {
            // Leaving function context - store then reset our function.
            $found[$file][] = $function;
            $function = null;
          } else if ($open_braces == 0) {
            // Leaving class context - reset class_extends.
            $class_extends = null;
          }
        } else if (static::token_matches("{", $tokens, $token_i) ||
                   static::token_matches(array(T_CURLY_OPEN), $tokens, $token_i)) {
          // Found "{"
          $open_braces++;
        } else if (($open_braces == 0) && static::token_matches(array(T_EXTENDS), $tokens, $token_i)) {
          // Found "extends" - if a string follows, set class_extends.
          if (static::token_matches(array(T_STRING), $tokens, $token_i + 1)) {
            $class_extends = $tokens[$token_i + 1][1];
          }
        } else if (($open_braces == 1) && static::token_matches(array(T_FUNCTION), $tokens, $token_i)) {
          // Found "function" - see if it's one we should check and, if so, build $function object.
          $line = $tokens[$token_i][2];

          // Search backwards to check visibility: "private function", "protected function",
          // "private static function", or "protected static function".
          $previous = array($tokens[$token_i - 1][0], $tokens[$token_i - 2][0]);
          $is_private = in_array(T_PRIVATE, $previous) || in_array(T_PROTECTED, $previous);
          $is_static = in_array(T_STATIC, $previous);

          // Search forward to get function name
          do {
            $token_i++;
            $name = static::token_matches(array(T_STRING), $tokens, $token_i) ?
              $tokens[$token_i][1] : "";
          } while (!$name && ($token_i < count($tokens)));

          $is_rss_feed = (($name == "feed") && ($type = "rss"));
          $is_action = (substr($name, 0, 7) == "action_");

          if (!$is_private && ($name != "__construct") &&
              (!$is_static || $is_rss_feed) &&
              ($is_action || (substr($type, 0, 10) != "controller"))) {
            $function = new stdClass();
            $function->name = $name;
            $function->line = $line;
            $function->type = $type;
            $function->class_extends = $class_extends;
          }
        } else if (($open_braces >= 2) && $function) {
          // We're inside the body of a function - see if we can find a needle.
          foreach ($needle_types as $needle_type => $data) {
            foreach ($data as $needle) {
              if ($count = static::needle_matches($needle, $tokens, $token_i)) {
                $token_i += $count - 1;  // gets incremented by 1 more after end of loop
                $function->$needle_type = true;
              }
            }
          }
        }
      }
    }

    // Generate the report
    $new = TMPPATH . "controller_auth_data.txt";
    $fd = fopen($new, "wb");
    ksort($found);
    foreach ($found as $file => $functions) {
      foreach ($functions as $function) {
        $flags = array();
        if (($function->class_extends != "Rest") &&
            $function->uses_input &&
            !$function->checks_csrf) {
          $flags[] = "DIRTY_CSRF";
        }
        if (($function->class_extends != "Controller_Admin") &&
          !$function->checks_authorization) {
          $flags[] = "DIRTY_AUTH";
        }

        if ($flags) {
          // Only print if flags are found
          fprintf($fd, "%-75s %-30s %s\n", $file, $function->name, implode("|", $flags));
        }
      }

      // If we specified a class to extend, make sure it does.  This uses the last function
      // of the file as a proxy for the whole class (e.g. uses $function).
      if (($classes = $file_types[$function->type]["class_extends"]) &&
          !in_array($function->class_extends, $classes)) {
        fprintf($fd, "%-75s %-30s %s\n", $file, basename($file), "INVALID_CLASS_EXTENDS");
      }
    }
    fclose($fd);

    // Compare with the expected report from our golden file.
    $canonical = MODPATH . "gallery/tests/controller_auth_data.txt";
    exec("diff $canonical $new -I '#.*'", $output, $return_value);
    $this->assertFalse((bool)$return_value,
      "Controller auth golden file mismatch.  Output:\n" . implode("\n", $output));
  }

  static function token_matches($expected_token, &$tokens, $token_i) {
    if (!isset($tokens[$token_i])) {
      return false;
    }

    if (is_array($expected_token)) {
      for ($i = 0; $i < count($expected_token); $i++) {
        if ($expected_token[$i] != $tokens[$token_i][$i]) {
          return false;
        }
      }
      return true;
    } else {
      return $expected_token == $tokens[$token_i];
    }
  }

  static function needle_matches($expected_needle, &$tokens, $token_i) {
    $needle_tokens = token_get_all("<?$expected_needle?>");
    array_shift($needle_tokens);
    array_pop($needle_tokens);

    foreach ($needle_tokens as $i => $needle_token) {
      if (is_array($needle_token)) {
        // We don't need to match the line number
        unset($needle_token[2]);
      }

      if (!static::token_matches($needle_token, $tokens, $token_i + $i)) {
        return false;
      }
    }
    return count($needle_tokens);
  }
}
