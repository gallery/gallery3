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
class Kohana_Exception_Test extends Gallery_Unit_Test_Case {

  public function dump_test() {
    // Verify the override.
    $this->assert_equal('<small>string</small><span>(19)</span> "removed for display"',
        Kohana_Exception::dump("1a62761b836138c6198313911"));
    $this->assert_equal('<small>string</small><span>(14)</span> "original value"',
        Kohana_Exception::dump("original value"));
  }

  public function safe_dump_test() {
    // Verify the delegation.
    $this->assert_equal('<small>string</small><span>(19)</span> "removed for display"',
        Kohana_Exception::safe_dump("original value", "password"));
    $this->assert_equal('<small>string</small><span>(14)</span> "original value"',
        Kohana_Exception::safe_dump("original value", "meow"));
  }

  public function sanitize_for_dump_match_key_test() {
    $this->assert_equal("removed for display",
        Kohana_Exception::_sanitize_for_dump("original value", "password", 5));
    $this->assert_equal("original value",
        Kohana_Exception::_sanitize_for_dump("original value", "meow", 5));
  }

  public function sanitize_for_dump_match_key_loosely_test() {
    $this->assert_equal("removed for display",
        Kohana_Exception::_sanitize_for_dump("original value", "this secret key", 5));
  }

  public function sanitize_for_dump_match_value_test() {
    // Looks like a hash / secret value.
    $this->assert_equal("removed for display",
        Kohana_Exception::_sanitize_for_dump("p$2a178b841c6391d6368f131", "meow", 5));
    $this->assert_equal("original value",
        Kohana_Exception::_sanitize_for_dump("original value", "meow", 5));
  }

  public function sanitize_for_dump_array_test() {
    $var = array("safe" => "original value 1",
                 "some hash" => "original value 2",
                 "three" => "2a3728788982938293b9292");
    $expected = array("safe" => "original value 1",
                      "some hash" => "removed for display",
                      "three" => "removed for display");

    $this->assert_equal($expected,
        Kohana_Exception::_sanitize_for_dump($var, "ignored", 5));
  }

  public function sanitize_for_dump_nested_array_test() {
    $var = array("safe" => "original value 1",
                 "safe 2" => array("some hash" => "original value 2"));
    $expected = array("safe" => "original value 1",
                      "safe 2" => array("some hash" => "removed for display"));
    $this->assert_equal($expected,
        Kohana_Exception::_sanitize_for_dump($var, "ignored", 5));
  }

  public function sanitize_for_dump_user_test() {
    $user = new User_Model();
    $user->name = "john";
    $user->hash = "value 1";
    $user->email = "value 2";
    $user->full_name = "value 3";
    $this->assert_equal('User_Model object for "john" - details omitted for display',
        Kohana_Exception::_sanitize_for_dump($user, "ignored", 5));
  }

  public function sanitize_for_dump_database_test() {
    $db = new Kohana_Exception_Test_Database(
        array("connection" => array("user" => "john", "name" => "gallery_3"),
              "cache" => array()));
    $this->assert_equal("Kohana_Exception_Test_Database object - details omitted for display",
        Kohana_Exception::_sanitize_for_dump($db, "ignored", 5));
  }

  public function sanitize_for_dump_nested_database_test() {
    $db = new Kohana_Exception_Test_Database(
        array("connection" => array("user" => "john", "name" => "gallery_3"),
              "cache" => array()));
    $var = array("some" => "foo",
                 "bar" => $db);
    $this->assert_equal(
        array("some" => "foo",
              "bar (type: Kohana_Exception_Test_Database)" =>
              "Kohana_Exception_Test_Database object - details omitted for display"),
        Kohana_Exception::_sanitize_for_dump($var, "ignored", 5));
  }

  public function sanitize_for_dump_object_test() {
    $obj = new Kohana_Exception_Test_Class();
    $obj->password = "original value";
    $expected = array("var_1" => "val 1",
                      "protected: var_2" => "val 2",
                      "private: var_3" => "val 3",
                      "protected: hash" => "removed for display",
                      "private: email_address" => "removed for display",
                      "password" => "removed for display");
    $this->assert_equal($expected,
        Kohana_Exception::_sanitize_for_dump($obj, "ignored", 5));
  }

  public function sanitize_for_dump_nested_object_test() {
    $user = new User_Model();
    $user->name = "john";
    $obj = new Kohana_Exception_Test_Class();
    $obj->meow = new Kohana_Exception_Test_Class();
    $obj->woof = "original value";
    $obj->foo = array("bar" => $user);
    $expected = array("var_1" => "val 1",
                      "protected: var_2" => "val 2",
                      "private: var_3" => "val 3",
                      "protected: hash" => "removed for display",
                      "private: email_address" => "removed for display",
                      "meow (type: Kohana_Exception_Test_Class)" =>
                          array("var_1" => "val 1",
                                "protected: var_2" => "val 2",
                                "private: var_3" => "val 3",
                                "protected: hash" => "removed for display",
                                "private: email_address" => "removed for display"),
                      "woof" => "original value",
                      "foo" => array("bar (type: User_Model)" =>
                                     'User_Model object for "john" - details omitted for display'));
    $this->assert_equal($expected,
        Kohana_Exception::_sanitize_for_dump($obj, "ignored", 5));
  }
}

class Kohana_Exception_Test_Database extends Database {
  function __construct($config) { parent::__construct($config); }
  public function connect() {}
  public function disconnect() {}
  public function set_charset($charset) {}
  public function query_execute($sql) {}
  public function escape($value) {}
  public function list_constraints($table) {}
  public function list_fields($table) {}
  public function list_tables() {}
}

class Kohana_Exception_Test_Class {
  public $var_1 = "val 1";
  protected $var_2 = "val 2";
  private $var_3 = "val 3";
  protected $hash = "val 4";
  private $email_address = "val 5";
  function __set($name, $val) {
    $this->$name = $val;
  }
}