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
class SafeString_Test extends Gallery_Unit_Test_Case {
  public function toString_escapes_for_html_test() {
    $safe_string = new SafeString("hello <p>world</p>");
    $this->assert_equal("hello &lt;p&gt;world&lt;/p&gt;",
                        $safe_string);
  }

  public function toString_for_safe_string_test() {
    $safe_string = SafeString::of_safe_html("hello <p>world</p>");
    $this->assert_equal("hello <p>world</p>",
                        $safe_string);
  }

  public function for_html_test() {
    $safe_string = new SafeString("hello <p>world</p>");
    $this->assert_equal("hello &lt;p&gt;world&lt;/p&gt;",
                        $safe_string->for_html());
  }

  public function safestring_of_safestring_test() {
    $safe_string = new SafeString("hello <p>world</p>");
    $safe_string_2 = new SafeString($safe_string);
    $this->assert_true($safe_string_2 instanceof SafeString);
    $raw_string = $safe_string_2->unescaped();
    $this->assert_false(is_object($raw_string));
    $this->assert_equal("hello <p>world</p>", $raw_string);
    $this->assert_equal("hello &lt;p&gt;world&lt;/p&gt;", $safe_string_2);
  }

  public function for_js_test() {
    $safe_string = new SafeString('"<em>Foo</em>\'s bar"');
    $js_string = $safe_string->for_js();
    $this->assert_equal('"\\"<em>Foo<\\/em>\'s bar\\""',
                        $js_string);
  }

  public function for_html_attr_test() {
    $safe_string = new SafeString('"<em>Foo</em>\'s bar"');
    $attr_string = $safe_string->for_html_attr();
    $this->assert_equal('&quot;&lt;em&gt;Foo&lt;/em&gt;&#039;s bar&quot;',
                        $attr_string);
  }

  public function for_html_attr_with_safe_html_test() {
    $safe_string = SafeString::of_safe_html('"<em>Foo</em>\'s bar"');
    $attr_string = $safe_string->for_html_attr();
    $this->assert_equal('&quot;<em>Foo</em>&#039;s bar&quot;',
                        $attr_string);
  }

  public function string_safestring_equality_test() {
    $safe_string = new SafeString("hello <p>world</p>");
    $this->assert_equal("hello <p>world</p>",
                        $safe_string->unescaped());
    $escaped_string = "hello &lt;p&gt;world&lt;/p&gt;";
    $this->assert_equal($escaped_string, $safe_string);

    $this->assert_true($escaped_string == $safe_string);
    $this->assert_false($escaped_string === $safe_string);
    $this->assert_false("meow" == $safe_string);
  }

  public function of_test() {
    $safe_string = SafeString::of("hello <p>world</p>");
    $this->assert_equal("hello <p>world</p>", $safe_string->unescaped());
  }

  public function of_safe_html_test() {
    $safe_string = SafeString::of_safe_html("hello <p>world</p>");
    $this->assert_equal("hello <p>world</p>", $safe_string->for_html());
  }

  public function purify_test() {
    $safe_string = SafeString::purify("hello <p  >world</p>");
    $expected = (class_exists("purifier") && method_exists("purifier", "purify"))
      ? "hello <p>world</p>"
      : "hello &lt;p  &gt;world&lt;/p&gt;";
    $this->assert_equal($expected, $safe_string);
  }

  public function purify_twice_test() {
    $safe_string = SafeString::purify("hello <p  >world</p>");
    $safe_string_2 = SafeString::purify($safe_string);
    $expected = (class_exists("purifier") && method_exists("purifier", "purify"))
      ? "hello <p>world</p>"
      : "hello &lt;p  &gt;world&lt;/p&gt;";
    $this->assert_equal($expected, $safe_string_2);
  }

  public function purify_safe_html_test() {
    $safe_string = SafeString::of_safe_html("hello <p  >world</p>");
    $actual = SafeString::purify($safe_string);
    $this->assert_equal("hello <p  >world</p>", $actual);
  }

  public function of_fluid_api_test() {
    $escaped_string = SafeString::of("Foo's bar")->for_js();
    $this->assert_equal('"Foo\'s bar"', $escaped_string);
  }

  public function safestring_of_safestring_preserves_safe_status_test() {
    $safe_string = SafeString::of_safe_html("hello's <p>world</p>");
    $safe_string_2 = new SafeString($safe_string);
    $this->assert_equal("hello's <p>world</p>", $safe_string_2);
    $this->assert_equal('"hello\'s <p>world<\\/p>"', $safe_string_2->for_js());
  }

  public function safestring_of_safestring_preserves_html_safe_status_test() {
    $safe_string = SafeString::of_safe_html("hello's <p>world</p>");
    $safe_string_2 = new SafeString($safe_string);
    $this->assert_equal("hello's <p>world</p>", $safe_string_2);
    $this->assert_equal('"hello\'s <p>world<\\/p>"', $safe_string_2->for_js());
  }

  public function safestring_of_safestring_safe_status_override_test() {
    $safe_string = new SafeString("hello <p>world</p>");
    $safe_string_2 = SafeString::of_safe_html($safe_string);
    $this->assert_equal("hello <p>world</p>", $safe_string_2);
  }
}
