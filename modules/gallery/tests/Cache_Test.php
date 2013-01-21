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
class Cache_Test extends Gallery_Unit_Test_Case {
  private $_driver;
  public function setup() {
    db::build()->delete("caches")->execute();
    $this->_driver = new Cache_Database_Driver();
  }

  private function _exists($id) {
    return db::build()
      ->where("key", "=", $id)
      ->where("expiration", ">=", time())
      ->limit("1")
      ->count_records("caches") > 0;
  }

  public function cache_exists_test_helper_function_test() {
    $this->assert_false($this->_exists("test_key"), "test_key should not be defined");

    $id = random::hash();
    db::build()
      ->insert("caches")
      ->columns("key", "tags", "expiration", "cache")
      ->values($id, "<tag1>, <tag2>", 84600 + time(), serialize("some test data"))
      ->execute();

    $this->assert_true($this->_exists($id), "test_key should be defined");
  }

  public function cache_get_test() {
    $id = random::hash();

    db::build()
      ->insert("caches")
      ->columns("key", "tags", "expiration", "cache")
      ->values($id, "<tag1>, <tag2>", 84600 + time(), serialize("some test data"))
      ->execute();

    $data = $this->_driver->get(array($id));
    $this->assert_equal("some test data", $data, "cached data should match");

    $data = $this->_driver->get(array(""));
    $this->assert_equal(null, $data, "cached data should not be found");
  }

  public function cache_set_test() {
    $id = random::hash();
    $original_data = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set(array($id => $original_data), array("tag1", "tag2"), 84600);

    $data = $this->_driver->get(array($id));
    $this->assert_equal($original_data, $data, "cached data should match");
  }

  public function cache_get_tag_test() {
    $id1 = random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set(array($id1 => $value1), array("tag1", "tag2"), 84600);

    $id2 = random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set(array($id2 => $value2), array("tag2", "tag3"), 84600);

    $id3 = random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set(array($id3 => $value3), array("tag3", "tag4"), 84600);

    $data = $this->_driver->get_tag(array("tag2"));

    $expected = array($id1 => $value1, $id2 => $value2);
    ksort($expected);
    $this->assert_equal($expected, $data, "Expected id1 & id2");

    $data = $this->_driver->get_tag(array("tag4"));
    $this->assert_equal(array($id3 => $value3), $data, "Expected id3");
  }

  public function cache_delete_id_test() {
    $id1 = random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set(array($id1 => $value1), array("tag1", "tag2"), 84600);

    $id2 = random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set(array($id2 => $value2), array("tag2", "tag3"), 846000);

    $id3 = random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set(array($id3 => $value3), array("tag3", "tag4"), 84600);

    $this->_driver->delete(array($id1));

    $this->assert_false($this->_exists($id1), "$id1 should have been deleted");
    $this->assert_true($this->_exists($id2), "$id2 should not have been deleted");
    $this->assert_true($this->_exists($id3), "$id3 should not have been deleted");
  }

  public function cache_delete_tag_test() {
    $id1 = random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set(array($id1 => $value1), array("tag1", "tag2"), 84600);

    $id2 = random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set(array($id2 => $value2), array("tag2", "tag3"), 846000);

    $id3 = random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set(array($id3 => $value3), array("tag3", "tag4"), 84600);

    $data = $this->_driver->delete_tag(array("tag3"));

    $this->assert_true($this->_exists($id1), "$id1 should not have been deleted");
    $this->assert_false($this->_exists($id2), "$id2 should have been deleted");
    $this->assert_false($this->_exists($id3), "$id3 should have been deleted");
  }

  public function cache_delete_all_test() {
    $id1 = random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set(array($id1 => $value1), array("tag1", "tag2"), 84600);

    $id2 = random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set(array($id2 => $value2), array("tag2", "tag3"), 846000);

    $id3 = random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set(array($id3 => $value3), array("tag3", "tag4"), 84600);

    $data = $this->_driver->delete(true);

    $this->assert_false($this->_exists($id1), "$id1 should have been deleted");
    $this->assert_false($this->_exists($id2), "$id2 should have been deleted");
    $this->assert_false($this->_exists($id3), "$id3 should have been deleted");
  }
}