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
class Url_Security_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    $this->save = array(Router::$current_uri, Router::$complete_uri, $_GET);
  }

  public function teardown() {
    list(Router::$current_uri, Router::$complete_uri, $_GET) = $this->save;
  }

  public function xss_in_current_url_test() {
    Router::$current_uri = "foo/<xss>/bar";
    Router::$complete_uri = "foo/<xss>/bar?foo=bar";
    $this->assert_same("foo/&lt;xss&gt;/bar", url::current());
    $this->assert_same("foo/&lt;xss&gt;/bar?foo=bar", url::current(true));
  }

  public function xss_in_merged_url_test() {
    Router::$current_uri = "foo/<xss>/bar";
    Router::$complete_uri = "foo/<xss>/bar?foo=bar";
    $_GET = array("foo" => "bar");
    $this->assert_same("foo/&lt;xss&gt;/bar?foo=bar", url::merge(array()));
    $this->assert_same("foo/&lt;xss&gt;/bar?foo=bar&amp;a=b", url::merge(array("a" => "b")));
  }
}