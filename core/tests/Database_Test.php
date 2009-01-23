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
class Database_Test extends Unit_Test_Case {
  function simple_where_test() {
    $sql = Database::instance()
      ->where("a", 1)
      ->where("b", 2)
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same("SELECT * WHERE `a` = 1 AND `b` = 2", $sql);
  }

  function compound_where_test() {
    $sql = Database::instance()
      ->where("outer1", 1)
      ->open_paren()
      ->where("inner1", 1)
      ->orwhere("inner2", 2)
      ->close_paren()
      ->where("outer2", 2)
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT * WHERE `outer1` = 1 AND (`inner1` = 1 OR `inner2` = 2) AND `outer2` = 2",
      $sql);
  }

  function group_first_test() {
    $sql = Database::instance()
      ->open_paren()
      ->where("inner1", 1)
      ->orwhere("inner2", 2)
      ->close_paren()
      ->where("outer1", 1)
      ->where("outer2", 2)
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT * WHERE (`inner1` = 1 OR `inner2` = 2) AND `outer1` = 1 AND `outer2` = 2",
      $sql);
  }

  function where_array_test() {
    $sql = Database::instance()
      ->where("outer1", 1)
      ->open_paren()
      ->where("inner1", 1)
      ->orwhere(array("inner2" => 2, "inner3" => 3))
      ->close_paren()
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT * WHERE `outer1` = 1 AND (`inner1` = 1 OR `inner2` = 2 OR `inner3` = 3)",
      $sql);
  }

  function notlike_test() {
    $sql = Database::instance()
      ->where("outer1", 1)
      ->open_paren()
      ->ornotlike("inner1", 1)
      ->close_paren()
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT * WHERE `outer1` = 1 OR ( `inner1` NOT LIKE '%1%')",
      $sql);
  }
}
