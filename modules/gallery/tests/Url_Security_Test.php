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
class Url_Security_Test extends Unittest_TestCase {
  public function test_xss_in_current_url() {
    throw new Exception("fixme");
    Route::$current_uri = "foo/<xss>/bar";
    Route::$complete_uri = "foo/<xss>/bar?foo=bar";
    $this->assertSame("foo/&lt;xss&gt;/bar", Request::current()->uri());
    $this->assertSame("foo/&lt;xss&gt;/bar?foo=bar", Request::current()->uri() . URL::query());
  }

  public function test_xss_in_merged_url() {
    throw new Exception("fixme");
    Route::$current_uri = "foo/<xss>/bar";
    Route::$complete_uri = "foo/<xss>/bar?foo=bar";
    $_GET = array("foo" => "bar");
    $this->assertSame("foo/&lt;xss&gt;/bar?foo=bar", URL::query(array()));
    $this->assertSame("foo/&lt;xss&gt;/bar?foo=bar&amp;a=b", URL::query(array("a" => "b")));
  }
}