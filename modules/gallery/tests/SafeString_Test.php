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
class SafeString_Test extends Unittest_TestCase {
  public function test_toString_escapes_for_html() {
    $safe_string = new SafeString("hello <p>world</p>");
    $this->assertEquals("hello &lt;p&gt;world&lt;/p&gt;",
                        $safe_string);
  }

  public function test_toString_for_safe_string() {
    $safe_string = SafeString::of_safe_html("hello <p>world</p>");
    $this->assertEquals("hello <p>world</p>",
                        $safe_string);
  }

  public function test_for_html() {
    $safe_string = new SafeString("hello <p>world</p>");
    $this->assertEquals("hello &lt;p&gt;world&lt;/p&gt;",
                        $safe_string->for_html());
  }

  public function test_safestring_of_safestring() {
    $safe_string = new SafeString("hello <p>world</p>");
    $safe_string_2 = new SafeString($safe_string);
    $this->assertTrue($safe_string_2 instanceof SafeString);
    $raw_string = $safe_string_2->unescaped();
    $this->assertFalse(is_object($raw_string));
    $this->assertEquals("hello <p>world</p>", $raw_string);
    $this->assertEquals("hello &lt;p&gt;world&lt;/p&gt;", $safe_string_2);
  }

  public function test_for_js() {
    $safe_string = new SafeString('"<em>Foo</em>\'s bar"');
    $js_string = $safe_string->for_js();
    $this->assertEquals('"\\"<em>Foo<\\/em>\'s bar\\""',
                        $js_string);
  }

  public function test_for_html_attr() {
    $safe_string = new SafeString('"<em>Foo</em>\'s bar"');
    $attr_string = $safe_string->for_html_attr();
    $this->assertEquals('&quot;&lt;em&gt;Foo&lt;/em&gt;&#039;s bar&quot;',
                        $attr_string);
  }

  public function test_for_html_attr_with_safe_html() {
    $safe_string = SafeString::of_safe_html('"<em>Foo</em>\'s bar"');
    $attr_string = $safe_string->for_html_attr();
    $this->assertEquals('&quot;<em>Foo</em>&#039;s bar&quot;',
                        $attr_string);
  }

  public function test_string_safestring_equality() {
    $safe_string = new SafeString("hello <p>world</p>");
    $this->assertEquals("hello <p>world</p>",
                        $safe_string->unescaped());
    $escaped_string = "hello &lt;p&gt;world&lt;/p&gt;";
    $this->assertEquals($escaped_string, $safe_string);

    $this->assertTrue($escaped_string == $safe_string);
    $this->assertFalse($escaped_string === $safe_string);
    $this->assertFalse("meow" == $safe_string);
  }

  public function test_of() {
    $safe_string = SafeString::of("hello <p>world</p>");
    $this->assertEquals("hello <p>world</p>", $safe_string->unescaped());
  }

  public function test_of_safe_html() {
    $safe_string = SafeString::of_safe_html("hello <p>world</p>");
    $this->assertEquals("hello <p>world</p>", $safe_string->for_html());
  }

  public function test_purify() {
    $safe_string = SafeString::purify("hello <p  >world</p>");
    $expected = (class_exists("Purifier") && method_exists("Purifier", "purify"))
      ? "hello <p>world</p>"
      : "hello &lt;p  &gt;world&lt;/p&gt;";
    $this->assertEquals($expected, $safe_string);
  }

  public function test_purify_twice() {
    $safe_string = SafeString::purify("hello <p  >world</p>");
    $safe_string_2 = SafeString::purify($safe_string);
    $expected = (class_exists("Purifier") && method_exists("Purifier", "purify"))
      ? "hello <p>world</p>"
      : "hello &lt;p  &gt;world&lt;/p&gt;";
    $this->assertEquals($expected, $safe_string_2);
  }

  public function test_purify_safe_html() {
    $safe_string = SafeString::of_safe_html("hello <p  >world</p>");
    $actual = SafeString::purify($safe_string);
    $this->assertEquals("hello <p  >world</p>", $actual);
  }

  public function test_of_fluid_api() {
    $escaped_string = SafeString::of("Foo's bar")->for_js();
    $this->assertEquals('"Foo\'s bar"', $escaped_string);
  }

  public function test_safestring_of_safestring_preserves_safe_status() {
    $safe_string = SafeString::of_safe_html("hello's <p>world</p>");
    $safe_string_2 = new SafeString($safe_string);
    $this->assertEquals("hello's <p>world</p>", $safe_string_2);
    $this->assertEquals('"hello\'s <p>world<\\/p>"', $safe_string_2->for_js());
  }

  public function test_safestring_of_safestring_preserves_html_safe_status() {
    $safe_string = SafeString::of_safe_html("hello's <p>world</p>");
    $safe_string_2 = new SafeString($safe_string);
    $this->assertEquals("hello's <p>world</p>", $safe_string_2);
    $this->assertEquals('"hello\'s <p>world<\\/p>"', $safe_string_2->for_js());
  }

  public function test_safestring_of_safestring_safe_status_override() {
    $safe_string = new SafeString("hello <p>world</p>");
    $safe_string_2 = SafeString::of_safe_html($safe_string);
    $this->assertEquals("hello <p>world</p>", $safe_string_2);
  }
}
