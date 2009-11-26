<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class Cache_Test extends Unit_Test_Case {
  private $_driver;
  public function setup() {
    Database::instance()->from("caches")->where("1", "=", "1")->delete();
    $this->_driver = new Cache_Database_Driver();
  }

  public function cache_exists_test() {
    $db = Database::instance();

    $this->assert_false($this->_driver->exists("test_key"), "test_key should not be defined");

    $id = md5(rand());
    $db->insert("caches", array("key" => $id, "tags" => "<tag1>, <tag2>",
                                "expiration" => 84600 + time(),
                                "cache" => serialize("some test data")));

    $this->assert_true($this->_driver->exists($id), "test_key should be defined");
  }

  public function cache_get_test() {
    $db = Database::instance();

    $id = md5(rand());
    $db->insert("caches", array("key" => $id, "tags" => "<tag1>, <tag2>",
                                "expiration" => 84600  + time(),
                                "cache" => serialize("some test data")));

    $data = $this->_driver->get($id);
    $this->assert_equal("some test data", $data, "cached data should match");

    $data = $this->_driver->get("");
    $this->assert_equal(null, $data, "cached data should not be found");
  }

  public function cache_set_test() {
    $db = Database::instance();

    $id = md5(rand());
    $original_data = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set($id, $original_data, array("tag1", "tag2"), 84600);

    $data = $this->_driver->get($id);
    $this->assert_equal($original_data, $data, "cached data should match");
  }

  public function cache_find_test() {
    $db = Database::instance();

    $id1 = md5(rand());
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set($id1, $value1, array("tag1", "tag2"), 84600);

    $id2 = md5(rand());
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set($id2, $value2, array("tag2", "tag3"), 84600);

    $id3 = md5(rand());
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set($id3, $value3, array("tag3", "tag4"), 84600);

    $data = $this->_driver->find("tag2");

    $expected = array($id1 => $value1, $id2 => $value2);
    ksort($expected);
    $this->assert_equal($expected, $data, "Expected id1 & id2");

    $data = $this->_driver->find("tag4");
    $this->assert_equal(array($id3 => $value3), $data, "Expected id3");
  }

  public function cache_delete_expired_test() {
    $db = Database::instance();

    $id1 = md5(rand());
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set($id1, $value1, array("tag1", "tag2"), -84600);

    $id2 = md5(rand());
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set($id2, $value2, array("tag2", "tag3"), -846000);

    $id3 = md5(rand());
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set($id3, $value3, array("tag3", "tag4"), -84600);

    $data = $this->_driver->delete_expired();

    $this->assert_false($this->_driver->exists($id1), "$id1 should have been deleted");
    $this->assert_false($this->_driver->exists($id2), "$id2 should have been deleted");
    $this->assert_false($this->_driver->exists($id3), "$id3 should have been deleted");
  }

  public function cache_delete_id_test() {
    $db = Database::instance();

    $id1 = md5(rand());
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set($id1, $value1, array("tag1", "tag2"), 84600);

    $id2 = md5(rand());
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set($id2, $value2, array("tag2", "tag3"), 846000);

    $id3 = md5(rand());
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set($id3, $value3, array("tag3", "tag4"), 84600);

    $this->_driver->delete($id1);

    $this->assert_false($this->_driver->exists($id1), "$id1 should have been deleted");
    $this->assert_true($this->_driver->exists($id2), "$id2 should not have been deleted");
    $this->assert_true($this->_driver->exists($id3), "$id3 should not have been deleted");
  }

  public function cache_delete_tag_test() {
    $db = Database::instance();

    $id1 = md5(rand());
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set($id1, $value1, array("tag1", "tag2"), 84600);

    $id2 = md5(rand());
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set($id2, $value2, array("tag2", "tag3"), 846000);

    $id3 = md5(rand());
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set($id3, $value3, array("tag3", "tag4"), 84600);

    $data = $this->_driver->delete("tag3", true);

    $this->assert_true($this->_driver->exists($id1), "$id1 should not have been deleted");
    $this->assert_false($this->_driver->exists($id2), "$id2 should have been deleted");
    $this->assert_false($this->_driver->exists($id3), "$id3 should have been deleted");
  }

  public function cache_delete_all_test() {
    $db = Database::instance();

    $id1 = md5(rand());
    $value1 = array("field1" => "value1", "field2" => "value2");
    $this->_driver->set($id1, $value1, array("tag1", "tag2"), 84600);

    $id2 = md5(rand());
    $value2 = array("field3" => "value3", "field4" => "value4");
    $this->_driver->set($id2, $value2, array("tag2", "tag3"), 846000);

    $id3 = md5(rand());
    $value3 = array("field5" => "value5", "field6" => "value6");
    $this->_driver->set($id3, $value3, array("tag3", "tag4"), 84600);

    $data = $this->_driver->delete(true);

    $this->assert_false($this->_driver->exists($id1), "$id1 should have been deleted");
    $this->assert_false($this->_driver->exists($id2), "$id2 should have been deleted");
    $this->assert_false($this->_driver->exists($id3), "$id3 should have been deleted");
  }
}