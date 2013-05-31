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
class HTML_Test extends Unittest_TestCase {
  public function test_clean() {
    $safe_string = HTML::clean("hello <p  >world</p>");
    $this->assertEquals("hello &lt;p  &gt;world&lt;/p&gt;",
                        $safe_string);
    $this->assertTrue($safe_string instanceof SafeString);
  }

  public function test_purify() {
    $safe_string = HTML::purify("hello <p  >world</p>");
    $expected = (class_exists("Purifier") && method_exists("Purifier", "purify"))
      ? "hello <p>world</p>"
      : "hello &lt;p  &gt;world&lt;/p&gt;";
    $this->assertEquals($expected, $safe_string->unescaped());
    $this->assertTrue($safe_string instanceof SafeString);
  }

  public function test_mark_clean() {
    $safe_string = HTML::mark_clean("hello <p  >world</p>");
    $this->assertTrue($safe_string instanceof SafeString);
    $safe_string_2 = HTML::clean($safe_string);
    $this->assertEquals("hello <p  >world</p>",
                        $safe_string_2);
  }

  public function test_js_string() {
    $string = HTML::js_string("hello's <p  >world</p>");
    $this->assertEquals('"hello\'s <p  >world<\\/p>"',
                        $string);
  }

  public function test_clean_attribute() {
    $safe_string = SafeString::of_safe_html("hello's <p  >world</p>");
    $safe_string = HTML::clean_attribute($safe_string);
    $this->assertEquals("hello&#039;s <p  >world</p>",
                        $safe_string);
  }
}