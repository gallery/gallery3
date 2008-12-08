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
    $menu = new Menu(true);
    $menu
      ->append(Menu::factory("link")->id("element_1"))
      ->append(Menu::factory("dialog")->id("element_2"))
      ->append(Menu::factory("submenu")->id("element_3")
               ->append(Menu::factory("link")->id("element_3_1")));

    $this->assert_equal("element_2", $menu->get("element_2")->id);
    $this->assert_equal("element_3_1", $menu->get("element_3")->get("element_3_1")->id);
  }
}