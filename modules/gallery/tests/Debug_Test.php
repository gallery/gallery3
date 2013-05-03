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
class Debug_Test extends Unittest_Testcase {

  public function test_dump() {
    // Verify the override.
    $this->assertEquals('<small>string</small><span>(19)</span> "removed for display"',
        Debug::dump("1a62761b836138c6198313911"));
    $this->assertEquals('<small>string</small><span>(14)</span> "original value"',
        Debug::dump("original value"));
  }

  public function test_safe_dump() {
    // Verify the delegation.
    $this->assertEquals('<small>string</small><span>(19)</span> "removed for display"',
        Debug::safe_dump("original value", "password"));
    $this->assertEquals('<small>string</small><span>(14)</span> "original value"',
        Debug::safe_dump("original value", "meow"));
  }

  public function test_sanitize_for_dump_match_key() {
    $this->assertEquals("removed for display",
        Debug::_sanitize_for_dump("original value", "password", 5));
    $this->assertEquals("original value",
        Debug::_sanitize_for_dump("original value", "meow", 5));
  }

  public function test_sanitize_for_dump_match_key_loosely() {
    $this->assertEquals("removed for display",
        Debug::_sanitize_for_dump("original value", "this secret key", 5));
  }

  public function test_sanitize_for_dump_match_value() {
    // Looks like a hash / secret value.
    $this->assertEquals("removed for display",
        Debug::_sanitize_for_dump("p$2a178b841c6391d6368f131", "meow", 5));
    $this->assertEquals("original value",
        Debug::_sanitize_for_dump("original value", "meow", 5));
  }

  public function test_sanitize_for_dump_array() {
    $var = array("safe" => "original value 1",
                 "some hash" => "original value 2",
                 "three" => "2a3728788982938293b9292");
    $expected = array("safe" => "original value 1",
                      "some hash" => "removed for display",
                      "three" => "removed for display");

    $this->assertEquals($expected,
        Debug::_sanitize_for_dump($var, "ignored", 5));
  }

  public function test_sanitize_for_dump_nested_array() {
    $var = array("safe" => "original value 1",
                 "safe 2" => array("some hash" => "original value 2"));
    $expected = array("safe" => "original value 1",
                      "safe 2" => array("some hash" => "removed for display"));
    $this->assertEquals($expected,
        Debug::_sanitize_for_dump($var, "ignored", 5));
  }

  public function test_sanitize_for_dump_user() {
    $user = new Model_User();
    $user->name = "john";
    $user->hash = "value 1";
    $user->email = "value 2";
    $user->full_name = "value 3";
    $this->assertEquals('Model_User object for "john" - details omitted for display',
        Debug::_sanitize_for_dump($user, "ignored", 5));
  }

  public function test_sanitize_for_dump_database() {
    $db = new Debug_Test_Database(
      "Debug_Test",
      array("connection" => array("user" => "john", "name" => "gallery_3"),
            "cache" => array()));
    $this->assertEquals("Debug_Test_Database object - details omitted for display",
        Debug::_sanitize_for_dump($db, "ignored", 5));
  }

  public function test_sanitize_for_dump_nested_database() {
    $db = new Debug_Test_Database(
      "Debug_Test",
      array("connection" => array("user" => "john", "name" => "gallery_3"),
            "cache" => array()));
    $var = array("some" => "foo",
                 "bar" => $db);
    $this->assertEquals(
        array("some" => "foo",
              "bar (type: Debug_Test_Database)" =>
              "Debug_Test_Database object - details omitted for display"),
        Debug::_sanitize_for_dump($var, "ignored", 5));
  }

  public function test_sanitize_for_dump_object() {
    $obj = new Debug_Test_Class();
    $obj->password = "original value";
    $expected = array("var_1" => "val 1",
                      "protected: var_2" => "val 2",
                      "private: var_3" => "val 3",
                      "protected: hash" => "removed for display",
                      "private: email_address" => "removed for display",
                      "password" => "removed for display");
    $this->assertEquals($expected,
        Debug::_sanitize_for_dump($obj, "ignored", 5));
  }

  public function test_sanitize_for_dump_nested_object() {
    $user = new Model_User();
    $user->name = "john";
    $obj = new Debug_Test_Class();
    $obj->meow = new Debug_Test_Class();
    $obj->woof = "original value";
    $obj->foo = array("bar" => $user);
    $expected = array("var_1" => "val 1",
                      "protected: var_2" => "val 2",
                      "private: var_3" => "val 3",
                      "protected: hash" => "removed for display",
                      "private: email_address" => "removed for display",
                      "meow (type: Debug_Test_Class)" =>
                          array("var_1" => "val 1",
                                "protected: var_2" => "val 2",
                                "private: var_3" => "val 3",
                                "protected: hash" => "removed for display",
                                "private: email_address" => "removed for display"),
                      "woof" => "original value",
                      "foo" => array("bar (type: Model_User)" =>
                                     'Model_User object for "john" - details omitted for display'));
    $this->assertEquals($expected,
        Debug::_sanitize_for_dump($obj, "ignored", 5));
  }
}

class Debug_Test_Database extends Database {
  function __construct($name, $config) { parent::__construct($name, $config); }
  public function connect() {}
  public function disconnect() {}
  public function set_charset($charset) {}
  public function query($type, $sql, $as_object=false, array $params=null) {}
  public function begin($mode=null) {}
  public function commit() {}
  public function rollback() {}
  public function list_tables($like=null) {}
  public function list_columns($table, $like=null, $add_prefix=true) {}
  public function escape($value) {}
}

class Debug_Test_Class {
  public $var_1 = "val 1";
  protected $var_2 = "val 2";
  private $var_3 = "val 3";
  protected $hash = "val 4";
  private $email_address = "val 5";
  function __set($name, $val) {
    $this->$name = $val;
  }
}