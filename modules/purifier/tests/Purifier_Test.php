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
/**
 * This is intended as a test for the Purifier class, not the HTMLPurifier library it uses (which
 * is already thoroughly tested against plethora of XSS attacks).  As such, the test cases used
 * aren't meant to illustrate XSS blocking ability but rather just that the Purifier class wraps
 * around HTMLPurifier correctly.
 */
class Purifier_Test extends Unittest_Testcase {
  public function test_clean_html_basic() {
    $this->assertEquals("hello wrld", Purifier::clean_html("hello w<o>rld"));
  }

  public function test_clean_html_recursion() {
    $this->assertEquals(array("hello wrld", "fo_<b>ar</b>"),
                        Purifier::clean_html(array("hello w<o>rld", "f<o>o_<b>ar</b>")));
  }

  public function test_add_config_group() {
    // Add test config group which doesn't tidy HTML
    $settings = array("HTML.TidyLevel" => "none");
    Purifier::add_config_group("test", $settings);
    // Test config group doesn't fix center
    $this->assertEquals('<center>Centered</center>',
      Purifier::clean_html('<center>Centered</center>', "test"));
    // Default config group still does
    $this->assertEquals('<div style="text-align:center;">Centered</div>',
      Purifier::clean_html('<center>Centered</center>'));
    // Test config group works when recursed
    $this->assertEquals(array('<center>Hello</center>', '<center>World</center>'),
      Purifier::clean_html(array('<center>Hello</center>', '<center>World</center>'), "test"));
  }

  public function test_clean_input_array_keys() {
    // Both clean and raw will fix "foo|bar|too" but trash "foo|bar" since "foo_bar" already exists
    $dirty = array("foo|bar" => "baz1", "foo_bar" => "baz2", "foo|bar|too" => "baz3");
    $raw   = array(                     "foo_bar" => "baz2", "foo_bar_too" => "baz3");
    $clean = array(                     "foo_bar" => "baz2", "foo_bar_too" => "baz3");
    $this->assertEquals(array($clean, $raw), Purifier::clean_input_array($dirty));
  }

  public function test_clean_input_array_charset() {
    // Note: this test file is not UTF8-encoded.
    $utf8 = utf8_encode("Àçéñöû");
    $iso88591 = utf8_decode($utf8);
    // Both clean and raw will convert values to UTF8
    $dirty = array("test" => $iso88591);
    $raw   = array("test" => $utf8);
    $clean = array("test" => $utf8);
    $this->assertEquals(array($clean, $raw), Purifier::clean_input_array($dirty));
  }

  public function test_clean_input_array_purifier() {
    // Raw will do nothing (keys already clean), clean will fix values
    $dirty = array("test" => "hello w<o>rld");
    $raw   = array("test" => "hello w<o>rld");
    $clean = array("test" => "hello wrld");
    $this->assertEquals(array($clean, $raw), Purifier::clean_input_array($dirty));
  }

  public function test_clean_input_array_recursion() {
    // Raw will recursively fix keys but not values, clean will recursively fix both
    $dirty = array("test" => "hello w<o>rld", "foo|bar" => array("fo|o" => "f<o>o", "ba|r" => "<b>ar</b>"));
    $raw   = array("test" => "hello w<o>rld", "foo_bar" => array("fo_o" => "f<o>o", "ba_r" => "<b>ar</b>"));
    $clean = array("test" => "hello wrld",    "foo_bar" => array("fo_o" => "fo",    "ba_r" => "<b>ar</b>"));
    $this->assertEquals(array($clean, $raw), Purifier::clean_input_array($dirty));
  }
}
