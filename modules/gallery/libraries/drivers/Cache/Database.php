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
/*
 * Based on the Cache_Sqlite_Driver developed by the Kohana Team
 */
class Cache_Database_Driver implements Cache_Driver {
  // Kohana database instance
  protected $db;

  /**
   * Tests that the storage location is a directory and is writable.
   */
  public function __construct() {
    // Open up an instance of the database
    $this->db = Database::instance();

    if (!$this->db->table_exists("caches")) {
      throw new Exception("@todo Cache table is not defined");
    }
  }

  /**
   * Checks if a cache id is already set.
   *
   * @param  string   cache id
   * @return boolean
   */
  public function exists($id) {
    $count = $this->db->count_records("caches", array("key" => $id, "expiration >=" => time()));
    return $count > 0;
  }

  /**
   * Sets a cache item to the given data, tags, and lifetime.
   *
   * @param   string   cache id to set
   * @param   string   data in the cache
   * @param   array    cache tags
   * @param   integer  lifetime
   * @return  bool
   */
  public function set($id, $data, array $tags = NULL, $lifetime) {
    if (!empty($tags)) {
      // Escape the tags, adding brackets so the tag can be explicitly matched
      $tags = "<" . implode(">,<", $tags) . ">";
    }

    // Cache Database driver expects unix timestamp
    if ($lifetime !== 0) {
      $lifetime += time();
    }

    if ($this->exists($id)) {
      $status = $this->db->update(
        "caches",
        array("tags" => $tags, "expiration" => $lifetime, "cache" => serialize($data)), array("key" => $id));
    } else {
      $status = $this->db->insert(
        "caches",
        array("key" => $id, "tags" => $tags, "expiration" => $lifetime, "cache" => serialize($data)));
    }

    return count($status) > 0;
  }

  /**
   * Finds an array of ids for a given tag.
   *
   * @param  string  tag name
   * @return array   of ids that match the tag
   */
  public function find($tag) {
    $db_result = $this->db->from("caches")
      ->like("tags", "<$tag>")
      ->get()
      ->result(true);

    // An array will always be returned
    $result = array();

    if ($db_result->count() > 0) {
      // Disable notices for unserializing
      $ER = error_reporting(~E_NOTICE);

      foreach ($db_result as $row) {
        // Add each cache to the array
        $result[$row->key] = unserialize($row->cache);
      }

      // Turn notices back on
      error_reporting($ER);
    }

    return $result;
  }

  /**
   * Fetches a cache item. This will delete the item if it is expired or if
   * the hash does not match the stored hash.
   *
   * @param  string  cache id
   * @return mixed|NULL
   */
  public function get($id) {
    $data = null;
    $result = $this->db->getwhere("caches", array("key" => $id));

    if (count($result) > 0) {
      $cache = $result->current();
      // Make sure the expiration is valid and that the hash matches
      if ($cache->expiration != 0 && $cache->expiration <= time()) {
        // Cache is not valid, delete it now
        $this->delete($cache->id);
      } else {
        // Disable notices for unserializing
        $ER = error_reporting(~E_NOTICE);

        // Return the valid cache data
        $data = unserialize($cache->cache);

        // Turn notices back on
        error_reporting($ER);
      }
    }

    return $data;
  }

  /**
   * Deletes a cache item by id or tag
   *
   * @param  string  cache id or tag, or true for "all items"
   * @param  bool    delete a tag
   * @return bool
   */
  public function delete($id, $tag = false) {
    $this->db->from("caches");
    if ($id === true) {
      $this->db->where(1);
      // Delete all caches
    } else if ($tag === true) {
      $this->db->like("tags", "<$id>");
    } else {
      $this->db->where("key", $id);
    }

    $status = $this->db->delete();

    return count($status) > 0;
  }

  /**
   * Deletes all cache files that are older than the current time.
   */
  public function delete_expired() {
    // Delete all expired caches
    $status = $this->db->from("caches")
      ->where(array("expiration !=" => 0, "expiration <=" => time()))
      ->delete();

    return count($status) > 0;
  }

} // End Cache Database Driver