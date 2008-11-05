<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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

class Theme_Test extends Unit_Test_Case {
  public function url_test() {
    $theme = new Theme("fake_theme", new View());
    $this->assert_equal("http://./themes/fake_theme/file", $theme->url("file"));
  }

  public function display_test() {
    $theme = new Theme("fake_theme", new View());
    $view = $theme->display("test_page", "Theme_Test_Mock_View");
    $this->assert_equal("test_page", $view->page_name);
  }

  public function item_test() {
    $v = new View();
    $v->item = "fake_item";
    $theme = new Theme("fake_theme", $v);
    $this->assert_equal("fake_item", $theme->item());
  }
}

class Theme_Test_Mock_View {
  public $page_name = null;

  public function __construct($page_name) {
    $this->page_name = $page_name;
  }
}