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
/**
 * Gallery Cache Database Driver
 */
class Gallery_Cache_Database extends Cache implements Cache_Tagging, Cache_GarbageCollect {

  /**
   * Retrieve a value based on an id
   *
   * @param   string  $id       id
   * @param   string  $default  default [Optional] Default value to return if id not found
   * @return  mixed
   * @throws  Cache_Exception
   */
  public function get($id, $default=null) {
    $cache = ORM::factory("Cache")->where("key", "=", $id)->find();

    if ($cache->loaded()) {
      // Make sure the expiration is valid and that the hash matches
      if ($cache->expiration != 0 && $cache->expiration <= time()) {
        // Cache is not valid, delete it now
        $this->delete($id);
      } else {
        // Temporarily disable notices for unserializing
        $ER = error_reporting(~E_NOTICE);
        $data = unserialize($cache->cache);
        error_reporting($ER);

        // Return the valid cache data
        return $data;
      }
    }
    // No valid cache data found - return default
    return $default;
  }

  /**
   * Set a value based on an id. Optionally add tags.
   *
   * @param   string   $id        id
   * @param   mixed    $data      data
   * @param   integer  $lifetime  lifetime [Optional]
   * @param   array    $tags      tags [Optional]
   * @return  boolean
   */
  public function set($id, $data, $lifetime=null, array $tags=null) {
    return (bool) $this->set_with_tags($id, $data, $lifetime, $tags);
  }

  /**
   * Delete a cache entry based on id
   *
   * @param   string  $id  id
   * @return  boolean
   * @throws  Cache_Exception
   */
  public function delete($id) {
    return (bool) DB::delete("caches")
      ->where("key", "=", $id)
      ->execute();
  }

  /**
   * Delete all cache entries
   *
   * @return  boolean
   */
  public function delete_all() {
    return (bool) DB::delete("caches")->execute();
  }

  /**
   * Set a value based on an id. Optionally add tags.
   *
   * @param   string   $id        id
   * @param   mixed    $data      data
   * @param   integer  $lifetime  lifetime [Optional]
   * @param   array    $tags      tags [Optional]
   * @return  boolean
   * @throws  Cache_Exception
   */
  public function set_with_tags($id, $data, $lifetime=null, array $tags=null) {
    // Serialize the data
    $data = serialize($data);

    // Normalise tags
    $tags = (null === $tags) ? null : ("<".implode(">,<", $tags).">");

    // Setup lifetime
    if ($lifetime === null) {
      $lifetime = (0 === Arr::get($this->_config, "default_expire", null)) ?
        0 :
        (Arr::get($this->_config, "default_expire", Cache::DEFAULT_EXPIRE) + time());
    } else {
      $lifetime = (0 === $lifetime) ? 0 : ((int) $lifetime + time());
    }

    $cache = ORM::factory("Cache")->where("key", "=", $id)->find();
    $cache->tags = $tags;
    $cache->expiration = $lifetime;
    $cache->cache = $data;
    if (!$cache->loaded()) {
      $cache->key = $id;
    }
    return (bool) $cache->save();
  }

  /**
   * Delete cache entries based on a tag
   *
   * @param   string  $tag  tag
   * @return  boolean
   * @throws  Cache_Exception
   */
  public function delete_tag($tag) {
    return (bool) DB::delete("caches")
      ->where("tags", "LIKE", "%" . Database::escape_for_like("<{$tag}>") . "%")
      ->execute();
  }

  /**
   * Find cache entries based on a tag
   *
   * @param   string  $tag  tag
   * @return  array
   * @throws  Cache_Exception
   */
  public function find($tag) {
    $result = array();

    // Temporarily disable notices for unserializing
    $ER = error_reporting(~E_NOTICE);
    foreach (ORM::factory("Cache")
             ->where("tags", "LIKE", "%" . Database::escape_for_like("<{$tag}>") . "%")
             ->find_all() as $cache) {
      $result[$cache->id] = unserialize($cache->cache);
    }
    error_reporting($ER);

    return $result;
  }

  /**
   * Garbage collection method that cleans any expired
   * cache entries from the cache.
   *
   * @return  void
   */
  public function garbage_collect() {
    DB::delete("caches")->where("expiration", "<", time())->execute();
  }

  /**
   * Tests whether an id exists or not
   *
   * @param   string  $id  id
   * @return  boolean
   * @throws  Cache_Exception
   */
  protected function exists($id) {
    return ORM::factory("Cache")
      ->where("key", "=", $id)
      ->find()
      ->loaded();
  }
}
