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
class Xss_Security_Test extends Gallery_Unit_Test_Case {
  public function find_unescaped_variables_in_views_test() {
    $found = array();
    foreach (glob("*/*/views/*.php") as $view) {
      // List of all tokens without whitespace, simplifying parsing.
      $tokens = array();
      foreach (token_get_all(file_get_contents($view)) as $token) {
        if (!is_array($token) || ($token[0] != T_WHITESPACE)) {
          $tokens[] = $token;
        }
      }

      $frame  = null;
      $script_block = 0;
      $in_script_block = false;
      $inline_html = "";
      $in_attribute_js_context = false;
      $in_attribute = false;
      $href_attribute_start = false;
      $preceded_by_quote = false;

      for ($token_number = 0; $token_number < count($tokens); $token_number++) {
        $token = $tokens[$token_number];

        // Are we in a <script> ... </script> block?
        if (is_array($token) && $token[0] == T_INLINE_HTML) {
          $inline_html = $token[1];
          // T_INLINE_HTML blocks can be split. Need to handle the case
          // where one token has "<scr" and the next has "ipt"
          while (self::_token_matches(array(T_INLINE_HTML), $tokens, $token_number + 1)) {
            $token_number++;
            $token = $tokens[$token_number];
            $inline_html .= $token[1];
          }

          $inline_html = str_replace("\n", " ", $inline_html);

          if ($frame) {
            $frame->expr_append($inline_html);
          }

          // Note: This approach won't catch <script src="..."> blocks if the src
          // URL is generated via < ? = url::site() ? > or some other PHP.
          // Assume that all such script blocks with a src URL have an
          // empty element body.
          // But we'll catch closing tags for such blocks, so don't keep track
          // of opening / closing tag count since it would be meaningless.

          // Handle multiple start / end blocks on the same line?
          $opening_script_pos = $closing_script_pos = -1;
          if (preg_match_all('{</script>}i', $inline_html, $matches, PREG_OFFSET_CAPTURE)) {
            $last_match = array_pop($matches[0]);
            if (is_array($last_match)) {
              $closing_script_pos = $last_match[1];
            } else {
              $closing_script_pos = $last_match;
            }
          }
          if (preg_match_all('{<script\b[^>]*>}i', $inline_html, $matches, PREG_OFFSET_CAPTURE)) {
            $last_match = array_pop($matches[0]);
            if (is_array($last_match)) {
              $opening_script_pos = $last_match[1];
            } else {
              $opening_script_pos = $last_match;
            }
          }
          if ($opening_script_pos != $closing_script_pos) {
            $in_script_block = $opening_script_pos > $closing_script_pos;
          }
        }

        $preceded_by_quote = preg_match('{[\'"]\s*$}i', $inline_html);

        $pos = false;
        if (($in_attribute || $in_attribute_js_context) &&
            ($pos = strpos($inline_html, $delimiter)) !== false) {
          $in_attribute_js_context = false;
          $in_attribute = false;
          $href_attribute_start = false;
        }
        if (!$in_attribute_js_context || !$in_attribute) {
          $pos = ($pos === false) ? 0 : $pos;
          if (preg_match('{\bhref\s*=\s*(")javascript:[^"]*$}i', $inline_html, $matches, 0, $pos) ||
              preg_match("{\bhref\s*=\s*(')javascript:[^']*$}i", $inline_html, $matches, 0, $pos) ||
              preg_match("{\bon[a-z]+\s*=\s*(')[^']*$}i", $inline_html, $matches, 0, $pos) ||
              preg_match('{\bon[a-z]+\s*=\s*(")[^"]*$}i', $inline_html, $matches, 0, $pos)) {
            $in_attribute_js_context = true;
            $in_attribute = true;
            $delimiter = $matches[1];
            $inline_html = "";
          } else if (preg_match('{\b([a-z]+)\s*=\s*(")([^"]*)$}i', $inline_html, $matches, 0, $pos) ||
                     preg_match("{\b([a-z]+)\s*=\s*(')([^']*)$}i", $inline_html, $matches, 0, $pos)) {
            $in_attribute = true;
            $delimiter = $matches[2];
            $inline_html = "";
            $href_attribute_start = strtolower($matches[1]) == "href" && empty($matches[3]);
          }
        }

        // Look and report each instance of < ? = ... ? >
        if (!is_array($token)) {
          // A single char token, e.g: ; ( )
          if ($frame) {
            $frame->expr_append($token);
          }
        } else if ($token[0] == T_OPEN_TAG_WITH_ECHO) {
          // No need for a stack here - assume < ? = cannot be nested.
          $frame = self::_create_frame($token, $in_script_block,
                                       $href_attribute_start, $in_attribute_js_context,
                                       $in_attribute, $preceded_by_quote);
          $href_attribute_start = false;
        } else if ($frame && $token[0] == T_CLOSE_TAG) {
          // Store the < ? = ... ? > block that just ended here.
          $found[$view][] = $frame;
          $frame = null;
        } else if ($frame && $token[0] == T_VARIABLE) {
          $frame->expr_append($token[1]);
          if ($token[1] == '$theme') {
            if (self::_token_matches(array(T_OBJECT_OPERATOR, "->"), $tokens, $token_number + 1) &&
                self::_token_matches(array(T_STRING), $tokens, $token_number + 2) &&
                in_array($tokens[$token_number + 2][1],
                         array("thumb_proportion", "site_menu", "album_menu", "tag_menu", "photo_menu",
                               "context_menu", "pager", "site_status", "messages", "album_blocks",
                               "album_bottom", "album_top", "body_attributes", "credits",
                               "dynamic_bottom", "dynamic_top", "footer", "head", "header_bottom",
                               "header_top", "page_bottom", "page_top", "photo_blocks", "photo_bottom",
                               "photo_top", "resize_bottom", "resize_top", "sidebar_blocks", "sidebar_bottom",
                               "sidebar_top", "thumb_bottom", "thumb_info", "thumb_top",
                               "movie_menu")) &&
                self::_token_matches("(", $tokens, $token_number + 3)) {

              $method = $tokens[$token_number + 2][1];
              $frame->expr_append("->$method(");

              $token_number += 3;
              $token = $tokens[$token_number];

              $frame->is_safe_html(true);
            } else if (self::_token_matches(array(T_OBJECT_OPERATOR, "->"), $tokens, $token_number + 1) &&
                       self::_token_matches(array(T_STRING), $tokens, $token_number + 2) &&
                       in_array($tokens[$token_number + 2][1],
                                array("css", "script", "url")) &&
                       self::_token_matches("(", $tokens, $token_number + 3) &&
                       // Only allow constant strings here
                       self::_token_matches(array(T_CONSTANT_ENCAPSED_STRING), $tokens, $token_number + 4)) {

              $method = $tokens[$token_number + 2][1];
              $frame->expr_append("->$method(");

              $token_number += 4;
              $token = $tokens[$token_number];

              $frame->is_safe_html(true);
            }
          }
        } else if ($frame && $token[0] == T_STRING) {
          $frame->expr_append($token[1]);
          // t() and t2() are special in that they're guaranteed to return a SafeString().
          if (in_array($token[1], array("t", "t2"))) {
            if (self::_token_matches("(", $tokens, $token_number + 1)) {
              $frame->is_safe_html(true);
              $frame->expr_append("(");

              $token_number++;
              $token = $tokens[$token_number];
            }
          } else if ($token[1] == "SafeString") {
            // Looking for SafeString::of(...
            if (self::_token_matches(array(T_DOUBLE_COLON, "::"), $tokens, $token_number + 1) &&
                self::_token_matches(array(T_STRING), $tokens, $token_number + 2) &&
                in_array($tokens[$token_number + 2][1], array("of", "purify")) &&
                self::_token_matches("(", $tokens, $token_number + 3)) {
              // Not checking for of_safe_html(). We want such calls to be marked dirty (thus reviewed).

              $frame->is_safe_html(true);

              $method = $tokens[$token_number + 2][1];
              $frame->expr_append("::$method(");

              $token_number += 3;
              $token = $tokens[$token_number];
            }
          } else if ($token[1] == "json_encode") {
            if (self::_token_matches("(", $tokens, $token_number + 1)) {
              $frame->is_safe_js(true);
              $frame->expr_append("(");

              $token_number++;
              $token = $tokens[$token_number];
            }
          } else if ($token[1] == "url") {
            // url methods return safe HTML
            if (self::_token_matches(array(T_DOUBLE_COLON, "::"), $tokens, $token_number + 1) &&
                self::_token_matches(array(T_STRING), $tokens, $token_number + 2) &&
                in_array($tokens[$token_number + 2][1],
                         array("site", "current", "base", "file", "abs_site", "abs_current",
                               "abs_file", "merge")) &&
                self::_token_matches("(", $tokens, $token_number + 3)) {
              $frame->is_safe_html(true);
              $frame->is_safe_href_attr(true);
              $frame->is_safe_attr(true);

              $method = $tokens[$token_number + 2][1];
              $frame->expr_append("::$method(");

              $token_number += 3;
              $token = $tokens[$token_number];
            }
          } else if ($token[1] == "html") {
            if (self::_token_matches(array(T_DOUBLE_COLON, "::"), $tokens, $token_number + 1) &&
                self::_token_matches(array(T_STRING), $tokens, $token_number + 2) &&
                in_array($tokens[$token_number + 2][1],
                         array("clean", "purify", "js_string", "clean_attribute")) &&
                self::_token_matches("(", $tokens, $token_number + 3)) {
              // Not checking for mark_clean(). We want such calls to be marked dirty (thus reviewed).

              $method = $tokens[$token_number + 2][1];
              $frame->expr_append("::$method(");

              $token_number += 3;
              $token = $tokens[$token_number];

              if ("js_string" == $method) {
                $frame->is_safe_js(true);
              } else {
                $frame->is_safe_html(true);
              }
              if ("clean_attribute" == $method) {
                $frame->is_safe_attr(true);
              }
            }
          }
        } else if ($frame && $token[0] == T_OBJECT_OPERATOR) {
          $frame->expr_append($token[1]);

          if (self::_token_matches(array(T_STRING), $tokens, $token_number + 1) &&
              in_array($tokens[$token_number + 1][1],
                       array("for_js", "for_html", "purified_html", "for_html_attr")) &&
              self::_token_matches("(", $tokens, $token_number + 2)) {
            $method = $tokens[$token_number + 1][1];
            $frame->expr_append("$method(");

            $token_number += 2;
            $token = $tokens[$token_number];

            if ("for_js" == $method) {
              $frame->is_safe_js(true);
            } else {
              $frame->is_safe_html(true);
            }
            if ("for_html_attr" == $method) {
              $frame->is_safe_attr(true);
            }
          }
        } else if ($frame) {
          $frame->expr_append($token[1]);
        }
      }
    }

    /*
     * Generate the report
     *
     * States for uses of < ? = X ? >:
     * DIRTY_JS:
     *   In <script> block
     *     X can be anything without calling ->for_js()
     *   At the start of a href= attribute
     *     X = anything but a url method
     *   In href="javascript: or onclick="...":
     *     X = anything (manual review required)
     * DIRTY:
     *   Outside <script> block:
     *     X can be anything without a call to ->for_html() or ->purified_html()
     * CLEAN:
     *   Outside <script> block:
     *     X = is SafeString (t(), t2(), url::site())
     *     X = * and for_html() or purified_html() is called
     *   Inside <script> block:
     *     X = * with ->for_js() or json_encode(...)
     *   Start of href attribute:
     *     X = url method
     */
    $new = TMPPATH . "xss_data.txt";
    $fd = fopen($new, "wb");
    ksort($found);
    foreach ($found as $view => $frames) {
      foreach ($frames as $frame) {
        $state = "DIRTY";
        if ($frame->in_script_block() && $frame->in_href_attribute()) {
          // This parser assumes this state does not occur.
          $state = "ILLEGAL";
        } else if ($frame->in_script_block()) {
          $state = "DIRTY_JS";
          if ($frame->is_safe_js() && !$frame->preceded_by_quote()) {
            $state = "CLEAN";
          }
        } else if ($frame->in_attribute_js_context()) {
          // Manual review required
          $state = "DIRTY_JS";
        } else if ($frame->in_href_attribute()) {
          $state = "DIRTY_JS";
          if ($frame->is_safe_href_attr()) {
            $state = "CLEAN";
          }
        } else if ($frame->in_attribute()) {
          $state = "DIRTY_ATTR";
          if ($frame->is_safe_attr()) {
            $state = "CLEAN";
          }
        } else {
          if ($frame->is_safe_html()) {
            $state = "CLEAN";
          }
        }

        if ("CLEAN" == $state) {
          // Don't print CLEAN instances - No need to update the golden
          // file when adding / moving clean instances.
          continue;
        }

        fprintf($fd, "%-60s %-3s %-8s %s\n",
                $view, $frame->line(), $state, $frame->expr());
      }
    }
    fclose($fd);

    // Compare with the expected report from our golden file.
    $canonical = MODPATH . "gallery/tests/xss_data.txt";
    exec("diff $canonical $new", $output, $return_value);
    $this->assert_false(
      $return_value, "XSS golden file mismatch.  Output:\n" . implode("\n", $output) );
  }

  private static function _create_frame($token, $in_script_block,
                                        $href_attribute_start, $in_attribute_js_context,
                                        $in_attribute, $preceded_by_quote) {
    return new Xss_Security_Test_Frame($token[2], $in_script_block,
                                       $href_attribute_start, $in_attribute_js_context,
                                       $in_attribute, $preceded_by_quote);
  }

  private static function _token_matches($expected_token, &$tokens, $token_number) {
    if (!isset($tokens[$token_number])) {
      return false;
    }

    $token = $tokens[$token_number];

    if (is_array($expected_token)) {
      for ($i = 0; $i < count($expected_token); $i++) {
        if ($expected_token[$i] != $token[$i]) {
          return false;
        }
      }
      return true;
    } else {
      return $expected_token == $token;
    }
  }
}

class Xss_Security_Test_Frame {
  private $_expr = "";
  private $_in_script_block = false;
  private $_is_safe_html = false;
  private $_is_safe_js = false;
  private $_in_href_attribute = false;
  private $_is_safe_href_attr = false;
  private $_in_attribute_js_context = false;
  private $_in_attribute = false;
  private $_preceded_by_quote = false;
  private $_is_safe_attr = false;
  private $_line;

  function __construct($line_number, $in_script_block,
                       $href_attribute_start, $in_attribute_js_context,
                       $in_attribute, $preceded_by_quote) {
    $this->_line = $line_number;
    $this->_in_script_block = $in_script_block;
    $this->_in_href_attribute = $href_attribute_start;
    $this->_in_attribute_js_context = $in_attribute_js_context;
    $this->_in_attribute = $in_attribute;
    $this->_preceded_by_quote = $preceded_by_quote;
  }

  function expr() {
    return $this->_expr;
  }

  function expr_append($append_value) {
    return $this->_expr .= $append_value;
  }

  function in_script_block() {
    return $this->_in_script_block;
  }

  function in_href_attribute() {
    return $this->_in_href_attribute;
  }

  function in_attribute() {
    return $this->_in_attribute;
  }

  function in_attribute_js_context() {
    return $this->_in_attribute_js_context;
  }

  function is_safe_html($new_val=NULL) {
    if ($new_val !== NULL) {
      $this->_is_safe_html = (bool) $new_val;
    }
    return $this->_is_safe_html;
  }

  function is_safe_href_attr($new_val=NULL) {
    if ($new_val !== NULL) {
      $this->_is_safe_href_attr  = (bool) $new_val;
    }
    return $this->_is_safe_href_attr;
  }

  function is_safe_attr($new_val=NULL) {
    if ($new_val !== NULL) {
      $this->_is_safe_attr  = (bool) $new_val;
    }
    return $this->_is_safe_attr;
  }

  function is_safe_js($new_val=NULL) {
    if ($new_val !== NULL) {
      $this->_is_safe_js = (bool) $new_val;
    }
    return $this->_is_safe_js;
  }

  function preceded_by_quote() {
    return $this->_preceded_by_quote;
  }

  function line() {
    return $this->_line;
  }
}
