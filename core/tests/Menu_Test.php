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
class Menu_Test extends Unit_Test_Case {
  public function find_menu_item_test() {
    $test_menu = new Menu();
    $test_menu->append(new Menu_Link("test1"));
    $test_menu->append(new Menu_Link("test2"));
    $expected = new Menu_Link("test3");
    $test_menu->append($expected);
    $test_menu->append(new Menu_Link("test4"));

    $menu_item = $test_menu->get("test3");
    $this->assert_equal($expected, $menu_item);
  }

  public function insert_before_test() {
    $expected = new Menu();
    $expected->append(new Menu_Link("test-2"));
    $expected->append(new Menu_Link("test0"));
    $expected->append(new Menu_Link("test1"));
    $expected->append(new Menu_Link("test1b"));
    $expected->append(new Menu_Link("test2"));
    $expected->append(new Menu_Link("test4"));

    $test_menu = new Menu();
    $test_menu->append(new Menu_Link("test1"));
    $test_menu->append(new Menu_Link("test2"));
    $test_menu->append(new Menu_Link("test4"));
    $test_menu->insert_before("test2", new Menu_Link("test1b"));
    $test_menu->insert_before("test1", new Menu_Link("test0"));
    $test_menu->insert_before("test-1", new Menu_Link("test-2"));
    
    $this->assert_equal($expected, $test_menu);
  }

  public function insert_after_test() {
    $expected = new Menu();
    $expected->append(new Menu_Link("test1"));
    $expected->append(new Menu_Link("test2"));
    $expected->append(new Menu_Link("test3"));
    $expected->append(new Menu_Link("test4"));
    $expected->append(new Menu_Link("test5"));
    $expected->append(new Menu_Link("test7"));

    $test_menu = new Menu();
    $test_menu->append(new Menu_Link("test1"));
    $test_menu->append(new Menu_Link("test2"));
    $test_menu->append(new Menu_Link("test4"));
    $test_menu->insert_after("test2", new Menu_Link("test3"));
    $test_menu->insert_after("test4", new Menu_Link("test5"));
    $test_menu->insert_after("test6", new Menu_Link("test7"));
    
    $this->assert_equal($expected, $test_menu);
  }
}