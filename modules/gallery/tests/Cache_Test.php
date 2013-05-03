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
class Cache_Test extends Unittest_TestCase {
  protected $_driver;
  public function setup() {
    parent::setup();
    DB::delete("caches")->execute();
  }

  public function test_cache_get() {
    $key = Random::hash();

    DB::insert("caches")
      ->columns(array("key", "tags", "expiration", "cache"))
      ->values(array($key, "<tag1>, <tag2>", 84600 + time(), serialize("some test data")))
      ->execute();

    $data = Cache::instance()->get(array($key));
    $this->assertEquals("some test data", $data, "cached data should match");

    $data = Cache::instance()->get(array(""));
    $this->assertEquals(null, $data, "cached data should not be found");
  }

  public function test_cache_set() {
    $key = Random::hash();
    $original_data = array("field1" => "value1", "field2" => "value2");
    Cache::instance()->set($key, $original_data, 86400, array("tag1", "tag2"));

    $data = Cache::instance()->get(array($key));
    $this->assertEquals($original_data, $data, "cached data should match");
  }

  public function test_cache_get_tag() {
    $key1 = Random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    Cache::instance()->set($key1, $value1, 84600, array("tag1", "tag2"));

    $key2 = Random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    Cache::instance()->set($key2, $value2, 84600, array("tag2", "tag3"));

    $key3 = Random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    Cache::instance()->set($key3, $value3, 84600, array("tag3", "tag4"));

    $data = Cache::instance()->find("tag2");

    $expected = array($key1 => $value1, $key2 => $value2);
    ksort($expected);
    $this->assertEquals($expected, $data, "Expected key1 & key2");

    $data = Cache::instance()->find("tag4");
    $this->assertEquals(array($key3 => $value3), $data, "Expected key3");
  }

  public function test_cache_delete_key() {
    $key1 = Random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    Cache::instance()->set($key1, $value1, 84600, array("tag1", "tag2"));

    $key2 = Random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    Cache::instance()->set($key2, $value2, 846000, array("tag2", "tag3"));

    $key3 = Random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    Cache::instance()->set($key3, $value3, 84600, array("tag3", "tag4"));

    Cache::instance()->delete($key1);

    $this->assertNotExists($key1, "$key1 should have been deleted");
    $this->assertExists($key2, "$key2 should not have been deleted");
    $this->assertExists($key3, "$key3 should not have been deleted");
  }

  public function test_cache_delete_tag() {
    $key1 = Random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    Cache::instance()->set($key1, $value1, 84600, array("tag1", "tag2"));

    $key2 = Random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    Cache::instance()->set($key2, $value2, 846000, array("tag2", "tag3"));

    $key3 = Random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    Cache::instance()->set($key3, $value3, 84600, array("tag3", "tag4"));

    $data = Cache::instance()->delete_tag("tag3");

    $this->assertExists($key1, "$key1 should not have been deleted");
    $this->assertNotExists($key2, "$key2 should have been deleted");
    $this->assertNotExists($key3, "$key3 should have been deleted");
  }

  public function test_cache_delete_all() {
    $key1 = Random::hash();
    $value1 = array("field1" => "value1", "field2" => "value2");
    Cache::instance()->set($key1, $value1, 84600, array("tag1", "tag2"));

    $key2 = Random::hash();
    $value2 = array("field3" => "value3", "field4" => "value4");
    Cache::instance()->set($key2, $value2, 846000, array("tag2", "tag3"));

    $key3 = Random::hash();
    $value3 = array("field5" => "value5", "field6" => "value6");
    Cache::instance()->set($key3, $value3, 84600, array("tag3", "tag4"));

    $data = Cache::instance()->delete_all();

    $this->assertNotExists($key1, "$key1 should have been deleted");
    $this->assertNotExists($key2, "$key2 should have been deleted");
    $this->assertNotExists($key3, "$key3 should have been deleted");
  }

  /* Helper functions */

  protected function key_count($key) {
    return DB::select()
      ->from("caches")
      ->where("key", "=", $key)
      ->where("expiration", ">=", time())
      ->limit("1")
      ->execute()
      ->count();
  }

  protected function assertNotExists($key, $message=null) {
    $this->assertEquals(0, $this->key_count($key), $message);
  }

  protected function assertExists($key, $message=null) {
    $this->assertNotEquals(0, $this->key_count($key), $message);
  }
}