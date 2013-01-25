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
class Database_Test extends Gallery_Unit_Test_Case {
  function setup() {
    $config = Kohana_Config::instance();
    $config->set("database.mock.connection.type", "mock");
    $config->set("database.mock.cache", false);
    $config->set("database.mock.table_prefix", "g_");
  }

  function simple_where_test() {
    $sql = db::build("mock")
      ->select("some_column")
      ->from("some_table")
      ->where("a", "=", 1)
      ->where("b", "=", 2)
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same("SELECT [some_column] FROM [some_table] WHERE [a] = [1] AND [b] = [2]", $sql);
  }

  function compound_where_test() {
    $sql = db::build("mock")
      ->select()
      ->where("outer1", "=", 1)
      ->and_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->close()
      ->where("outer2", "=", 2)
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT [*] WHERE [outer1] = [1] AND ([inner1] = [1] OR [inner2] = [2]) AND [outer2] = [2]",
      $sql);
  }

  function group_first_test() {
    $sql = db::build("mock")
      ->select()
      ->and_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->close()
      ->where("outer1", "=", 1)
      ->where("outer2", "=", 2)
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT [*] WHERE ([inner1] = [1] OR [inner2] = [2]) AND [outer1] = [1] AND [outer2] = [2]",
      $sql);
  }

  function where_array_test() {
    $sql = db::build("mock")
      ->select()
      ->where("outer1", "=", 1)
      ->and_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->or_where("inner3", "=", 3)
      ->close()
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT [*] WHERE [outer1] = [1] AND ([inner1] = [1] OR [inner2] = [2] OR [inner3] = [3])",
      $sql);
  }

  function notlike_test() {
    $sql = db::build("mock")
      ->select()
      ->where("outer1", "=", 1)
      ->or_open()
      ->where("inner1", "NOT LIKE", "%1%")
      ->close()
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same(
      "SELECT [*] WHERE [outer1] = [1] OR ([inner1] NOT LIKE [%1%])",
      $sql);
  }

  function prefix_replacement_test() {
    $db = Database::instance("mock");
    $converted = $db->add_table_prefixes("CREATE TABLE IF NOT EXISTS {test} (
                   `id` int(9) NOT NULL auto_increment,
                   `name` varchar(32) NOT NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8");
    $expected = "CREATE TABLE IF NOT EXISTS `g_test` (
                   `id` int(9) NOT NULL auto_increment,
                   `name` varchar(32) NOT NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $this->assert_same($expected, $converted);

    $sql = "UPDATE {test} SET `name` = '{test string}' " .
        "WHERE `item_id` IN " .
        "  (SELECT `id` FROM {test} " .
        "  WHERE `left_ptr` >= 1 " .
        "  AND `right_ptr` <= 6)";
    $sql = $db->add_table_prefixes($sql);

    $expected = "UPDATE `g_test` SET `name` = '{test string}' " .
        "WHERE `item_id` IN " .
        "  (SELECT `id` FROM `g_test` " .
        "  WHERE `left_ptr` >= 1 " .
        "  AND `right_ptr` <= 6)";

    $this->assert_same($expected, $sql);
  }

  function prefix_replacement_for_rename_table_test() {
    $db = Database::instance("mock");
    $this->assert_same(
      "RENAME TABLE `g_test` TO `g_new_test`",
      $db->add_table_prefixes("RENAME TABLE {test} TO {new_test}"));
  }

  function prefix_no_replacement_test() {
    $sql = db::build("mock")
      ->from("test_tables")
      ->where("1", "=", "1")
      ->set(array("name" => "Test Name"))
      ->update()
      ->compile();
    $sql = str_replace("\n", " ", $sql);
    $this->assert_same("UPDATE [test_tables] SET [name] = [Test Name] WHERE [1] = [1]", $sql);
  }

  function escape_for_like_test() {
    // Note: literal double backslash is written as \\\
    $this->assert_same('basic\_test', Database::escape_for_like("basic_test"));
    $this->assert_same('\\\100\%\_test/', Database::escape_for_like('\100%_test/'));
  }
}

class Database_Mock extends Database {
  public function connect() {
  }

  public function disconnect() {
  }

  public function set_charset($charset) {
  }

  public function query_execute($sql) {
  }

  public function escape($val) {
  }

  public function list_constraints($table) {
  }

  public function list_fields($table) {
  }

  public function list_tables() {
    return array("test");
  }

  public function quote_column($val, $alias=null) {
    return $alias ? "[$val,$alias]" : "[$val]";
  }

  public function quote_table($val, $alias=null) {
    return $alias ? "[$val,$alias]" : "[$val]";
  }

  public function quote($val) {
    return "[$val]";
  }
}