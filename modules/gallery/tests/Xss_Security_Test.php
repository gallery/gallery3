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
class Xss_Security_Test extends Unit_Test_Case {
  public function find_unescaped_variables_in_views_test() {
    foreach (glob("*/*/views/*.php") as $view) {
      $expr = null;
      $level = 0;
      $php = 0;
      $str = null;
      $in_p_clean = 0;
      foreach (token_get_all(file_get_contents($view)) as $token) {
        if (false /* useful for debugging */) {
          if (is_array($token)) {
            printf("[$str] [$in_p_clean] %-15s %s\n", token_name($token[0]), $token[1]);
          } else {
            printf("[$str] [$in_p_clean] %-15s %s\n", "<char>", $token);
          }
        }

        // If we find a "(" after a "p::clean" then start counting levels of parens and assume
        // that we're inside a p::clean() call until we find the matching close paren.
        if ($token[0] == "(" && ($str == "p::clean" || $str == "p::purify")) {
          $in_p_clean = 1;
        } else if ($token[0] == "(" && $in_p_clean) {
          $in_p_clean++;
        } else if ($token[0] == ")" && $in_p_clean) {
          $in_p_clean--;
        }

        // Concatenate runs of strings for convenience, which we use above to figure out if we're
        // inside a p::clean() call or not
        if ($token[0] == T_STRING || $token[0] == T_DOUBLE_COLON) {
          $str .= $token[1];
        } else {
          $str = null;
        }

        // Scan for any occurrences of < ? = $variable ? > and store it in $expr
        if ($token[0] == T_OPEN_TAG_WITH_ECHO) {
          $php++;
        } else if ($php && $token[0] == T_CLOSE_TAG) {
          $php--;
        } else if ($php && $token[0] == T_VARIABLE) {
          if (!$expr) {
            $entry = array($token[2], $in_p_clean);
          }
          $expr .= $token[1];
        } else if ($expr) {
          if ($token[0] == T_OBJECT_OPERATOR) {
            $expr .= $token[1];
          } else if ($token[0] == T_STRING) {
            $expr .= $token[1];
          } else if ($token == "(") {
            $expr .= $token;
            $level++;
          } else if ($level > 0 && $token == ")") {
            $expr .= $token;
            $level--;
          } else if ($level > 0) {
            $expr .= is_array($token) ? $token[1] : $token;
          } else {
            $entry[] = $expr;
            $found[$view][] = $entry;
            $expr = null;
            $entry = null;
          }
        }
      }
    }

    $canonical = MODPATH . "gallery/tests/xss_data.txt";
    $new = TMPPATH . "xss_data.txt";
    $fd = fopen($new, "wb");
    ksort($found);
    foreach ($found as $view => $entries) {
      foreach ($entries as $entry) {
        fwrite($fd,
               sprintf("%-60s %-3s %-5s %s\n",
                       $view, $entry[0], $entry[1] ? "" : "DIRTY", $entry[2]));
      }
    }
    fclose($fd);

    exec("diff $canonical $new", $output, $return_value);
    $this->assert_false(
      $return_value, "XSS golden file mismatch.  Output:\n" . implode("\n", $output) );
  }
}
