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
class Html_Helper_Test extends Gallery_Unit_Test_Case {
  public function clean_test() {
    $safe_string = html::clean("hello <p  >world</p>");
    $this->assert_equal("hello &lt;p  &gt;world&lt;/p&gt;",
                        $safe_string);
    $this->assert_true($safe_string instanceof SafeString);
  }

  public function purify_test() {
    $safe_string = html::purify("hello <p  >world</p>");
    $expected = (class_exists("purifier") && method_exists("purifier", "purify"))
      ? "hello <p>world</p>"
      : "hello &lt;p  &gt;world&lt;/p&gt;";
    $this->assert_equal($expected, $safe_string->unescaped());
    $this->assert_true($safe_string instanceof SafeString);
  }

  public function mark_clean_test() {
    $safe_string = html::mark_clean("hello <p  >world</p>");
    $this->assert_true($safe_string instanceof SafeString);
    $safe_string_2 = html::clean($safe_string);
    $this->assert_equal("hello <p  >world</p>",
                        $safe_string_2);
  }

  public function js_string_test() {
    $string = html::js_string("hello's <p  >world</p>");
    $this->assert_equal('"hello\'s <p  >world<\\/p>"',
                        $string);
  }

  public function clean_attribute_test() {
    $safe_string = SafeString::of_safe_html("hello's <p  >world</p>");
    $safe_string = html::clean_attribute($safe_string);
    $this->assert_equal("hello&#039;s <p  >world</p>",
                        $safe_string);
  }
}