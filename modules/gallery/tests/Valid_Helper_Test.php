<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Valid_Helper_Test extends Gallery_Unit_Test_Case {
  public function basic_url_valid_test() {
    $this->assert_true(valid::url('http://example.org'));
    $this->assert_true(valid::url('https://example.org'));
    $this->assert_true(valid::url('http://192.168.1.1/'));
    $this->assert_true(valid::url('http://www.example.com/foo/bar/baz.html?query=string'));
  }

  public function urls_with_diacritics_test() {
    $this->assert_true(valid::url('http://example.com/naïve'));
    $this->assert_true(valid::url('http://example.com/query=naïve'));
  }
}
