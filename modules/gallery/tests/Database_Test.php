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
class Database_Test extends Unittest_TestCase {
  function test_simple_where() {
    $sql = DB::select("some_column")
      ->from("some_table")
      ->where("a", "=", 1)
      ->where("b", "=", 2)
      ->compile();
    $this->assertSame(
      "SELECT `some_column` FROM `g3_some_table` WHERE `a` = 1 AND `b` = 2",
      $sql);
  }

  function test_compound_where() {
    $sql = DB::select()
      ->from("table")
      ->where("outer1", "=", 1)
      ->and_where_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->and_where_close()
      ->where("outer2", "=", 2)
      ->compile();
    $this->assertSame(
      "SELECT * FROM `g3_table` WHERE `outer1` = 1 AND " .
      "(`inner1` = 1 OR `inner2` = 2) AND `outer2` = 2",
      $sql);
  }

  function test_group_first() {
    $sql = DB::select()
      ->from("table")
      ->and_where_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->and_where_close()
      ->where("outer1", "=", 1)
      ->where("outer2", "=", 2)
      ->compile();
    $this->assertSame(
      "SELECT * FROM `g3_table` WHERE (`inner1` = 1 OR `inner2` = 2) " .
      "AND `outer1` = 1 AND `outer2` = 2",
      $sql);
  }

  function test_where_array() {
    $sql = DB::select()
      ->from("table")
      ->where("outer1", "=", 1)
      ->and_where_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->or_where("inner3", "=", 3)
      ->and_where_close()
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame(
      "SELECT * FROM `g3_table` WHERE `outer1` = 1 AND " .
      "(`inner1` = 1 OR `inner2` = 2 OR `inner3` = 3)",
      $sql);
  }

  function test_notlike() {
    $sql = DB::select()
      ->from("table")
      ->where("outer1", "=", 1)
      ->or_where_open()
      ->where("inner1", "NOT LIKE", "%1%")
      ->or_where_close()
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame(
      "SELECT * FROM `g3_table` WHERE `outer1` = 1 OR (`inner1` NOT LIKE '%1%')",
      $sql);
  }

  function test_prefix_replacement() {
    $actual = Database::instance()->add_table_prefixes(
      "CREATE TABLE IF NOT EXISTS {test} (
         `id` int(9) NOT NULL auto_increment,
         `name` varchar(32) NOT NULL,
         PRIMARY KEY (`id`),
         UNIQUE KEY(`name`))
       ENGINE=InnoDB DEFAULT CHARSET=utf8");
    $expected =
      "CREATE TABLE IF NOT EXISTS `g3_test` (
         `id` int(9) NOT NULL auto_increment,
         `name` varchar(32) NOT NULL,
         PRIMARY KEY (`id`),
         UNIQUE KEY(`name`))
       ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $this->assertSame($expected, $actual);

    $actual = Database::instance()->add_table_prefixes(
      "UPDATE {test} SET `name` = '{test string}'
       WHERE `item_id` IN
       (SELECT `id` FROM {test} WHERE `left_ptr` >= 1 AND `right_ptr` <= 6)");
    $expected =
      "UPDATE `g3_test` SET `name` = '{test string}'
       WHERE `item_id` IN
       (SELECT `id` FROM `g3_test` WHERE `left_ptr` >= 1 AND `right_ptr` <= 6)";
    $this->assertSame($expected, $actual);
  }

  function test_prefix_replacement_for_rename_table() {
    $this->assertSame(
      "RENAME TABLE `g3_test` TO `g3_new_test`",
      Database::instance()->add_table_prefixes("RENAME TABLE {test} TO {new_test}"));
  }

  function test_prefix_no_replacement() {
    $sql =
    $this->assertSame(
      "UPDATE `g3_test_table` SET `name` = 'Test Name' WHERE `1` = '1'",
      DB::update("test_table")
      ->where("1", "=", "1")
      ->set(array("name" => "Test Name"))
      ->compile());
  }

  function test_escape_for_like() {
    // Note: literal double backslash is written as \\\
    $this->assertSame('basic\_test', Database::escape_for_like("basic_test"));
    $this->assertSame('\\\100\%\_test/', Database::escape_for_like('\100%_test/'));
  }
}
