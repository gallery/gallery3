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
class Database_Test extends Unittest_Testcase {
  function setup() {
    $config = Config::instance();
    $config->set("database.mock.connection.type", "mock");
    $config->set("database.mock.cache", false);
    $config->set("database.mock.table_prefix", "g_");
  }

  function simple_where_test() {
    $sql = DB::select("some_column")
      ->from("some_table")
      ->where("a", "=", 1)
      ->where("b", "=", 2)
      ->compile("mock");
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame("SELECT [some_column] FROM [some_table] WHERE [a] = [1] AND [b] = [2]", $sql);
  }

  function compound_where_test() {
    $sql = DB::select()
      ->where("outer1", "=", 1)
      ->and_where_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->and_where_close()
      ->where("outer2", "=", 2)
      ->compile("mock");
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame(
      "SELECT [*] WHERE [outer1] = [1] AND ([inner1] = [1] OR [inner2] = [2]) AND [outer2] = [2]",
      $sql);
  }

  function group_first_test() {
    $sql = DB::select()
      ->and_where_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->and_where_close()
      ->where("outer1", "=", 1)
      ->where("outer2", "=", 2)
      ->compile("mock");
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame(
      "SELECT [*] WHERE ([inner1] = [1] OR [inner2] = [2]) AND [outer1] = [1] AND [outer2] = [2]",
      $sql);
  }

  function where_array_test() {
    $sql = DB::select()
      ->where("outer1", "=", 1)
      ->and_where_open()
      ->where("inner1", "=", 1)
      ->or_where("inner2", "=", 2)
      ->or_where("inner3", "=", 3)
      ->and_where_close()
      ->compile("mock");
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame(
      "SELECT [*] WHERE [outer1] = [1] AND ([inner1] = [1] OR [inner2] = [2] OR [inner3] = [3])",
      $sql);
  }

  function notlike_test() {
    $sql = DB::select()
      ->where("outer1", "=", 1)
      ->or_where_open()
      ->where("inner1", "NOT LIKE", "%1%")
      ->or_where_close()
      ->compile("mock");
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame(
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
    $this->assertSame($expected, $converted);

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

    $this->assertSame($expected, $sql);
  }

  function prefix_replacement_for_rename_table_test() {
    $db = Database::instance("mock");
    $this->assertSame(
      "RENAME TABLE `g_test` TO `g_new_test`",
      $db->add_table_prefixes("RENAME TABLE {test} TO {new_test}"));
  }

  function prefix_no_replacement_test() {
    $sql = DB::select()
      ->from("test_tables")
      ->where("1", "=", "1")
      ->set(array("name" => "Test Name"))
      ->update()
      ->compile("mock");
    $sql = str_replace("\n", " ", $sql);
    $this->assertSame("UPDATE [test_tables] SET [name] = [Test Name] WHERE [1] = [1]", $sql);
  }

  function escape_for_like_test() {
    // Note: literal double backslash is written as \\\
    $this->assertSame('basic\_test', Database::escape_for_like("basic_test"));
    $this->assertSame('\\\100\%\_test/', Database::escape_for_like('\100%_test/'));
  }
}

class Database_Mock extends Gallery_Database {
  public function connect() {
  }

  public function disconnect() {
    return true;
  }

  public function set_charset($charset) {
  }

  public function query($type, $sql, $as_object=false, array $params=null) {
    return array($type, $sql, $as_object, $params);
  }

  public function begin($mode=null) {
    return true;
  }

  public function commit() {
    return true;
  }

  public function rollback() {
    return true;
  }

  public function list_tables($like=null) {
    return array("@todo put something reasonable here");
  }

  public function list_columns($table, $like=null, $add_prefix=true) {
    return array("@todo put something reasonable here");
  }

  public function escape($value) {
    return "[escaped:$value]";
  }

}