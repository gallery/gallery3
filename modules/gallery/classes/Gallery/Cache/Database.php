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

  protected $_db;

  /**
   * Sets up the PDO SQLite table and
   * initialises the PDO connection
   *
   * @param  array  $config  configuration
   * @throws  Cache_Exception
   */
  protected function __construct(array $config) {
    parent::__construct($config);
    $this->_db = Database::instance();
  }

  /**
   * Retrieve a value based on an id
   *
   * @param   string  $id       id
   * @param   string  $default  default [Optional] Default value to return if id not found
   * @return  mixed
   * @throws  Cache_Exception
   */
  public function get($id, $default=null) {
    $data = null;
    $result = DB::select()
      ->from("caches")
      ->where("key", "=", $id)
      ->as_object()
      ->execute();

    if ($result->count()) {
      $cache = $result->current();

      // Make sure the expiration is valid and that the hash matches
      if ($cache->expiration != 0 && $cache->expiration <= time()) {
        // Cache is not valid, delete it now
        $this->delete(array($cache->id));
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
   * Set a value based on an id. Optionally add tags.
   *
   * @param   string   $id        id
   * @param   mixed    $data      data
   * @param   integer  $lifetime  lifetime [Optional]
   * @return  boolean
   */
  public function set($id, $data, $lifetime = NULL)
  {
    return (bool) $this->set_with_tags($id, $data, $lifetime);
  }

  /**
   * Delete a cache entry based on id
   *
   * @param   string  $id  id
   * @return  boolean
   * @throws  Cache_Exception
   */
  public function delete($id)
  {
    return (bool)DB::delete("caches")
      ->where("id", "=", $id)
      ->execute();
  }

  /**
   * Delete all cache entries
   *
   * @return  boolean
   */
  public function delete_all()
  {
    // Prepare statement
    $statement = $this->_db->prepare('DELETE FROM caches');

    // Remove the entry
    try
    {
      $statement->execute();
    }
    catch (PDOException $e)
    {
      throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
    }

    return (bool) $statement->rowCount();
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

    // In Kohana 2 we also had an "ON DUPLICATE KEY UPDATE" stanza here.  Elided
    // for simplicity - we might want it back.
    return (bool) DB::insert(
      "caches",
      array("key", "tags", "expiration", "cache"))
      ->values(array($id, $tags, $lifetime, $data))
      ->execute();
  }

  /**
   * Delete cache entries based on a tag
   *
   * @param   string  $tag  tag
   * @return  boolean
   * @throws  Cache_Exception
   */
  public function delete_tag($tag)
  {
    // Prepare the statement
    $statement = $this->_db->prepare('DELETE FROM caches WHERE tags LIKE :tag');

    // Try to delete
    try
    {
      $statement->execute(array(':tag' => "%<{$tag}>%"));
    }
    catch (PDOException $e)
    {
      throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
    }

    return (bool) $statement->rowCount();
  }

  /**
   * Find cache entries based on a tag
   *
   * @param   string  $tag  tag
   * @return  array
   * @throws  Cache_Exception
   */
  public function find($tag)
  {
    // Prepare the statement
    $statement = $this->_db->prepare('SELECT id, cache FROM caches WHERE tags LIKE :tag');

    // Try to find
    try
    {
      if ( ! $statement->execute(array(':tag' => "%<{$tag}>%")))
      {
        return array();
      }
    }
    catch (PDOException $e)
    {
      throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
    }

    $result = array();

    while ($row = $statement->fetchObject())
    {
      // Disable notices for unserializing
      $ER = error_reporting(~E_NOTICE);

      $result[$row->id] = unserialize($row->cache);

      // Turn notices back on
      error_reporting($ER);
    }

    return $result;
  }

  /**
   * Garbage collection method that cleans any expired
   * cache entries from the cache.
   *
   * @return  void
   */
  public function garbage_collect()
  {
    // Create the sequel statement
    $statement = $this->_db->prepare('DELETE FROM caches WHERE expiration < :expiration');

    try
    {
      $statement->execute(array(':expiration' => time()));
    }
    catch (PDOException $e)
    {
      throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
    }
  }

  /**
   * Tests whether an id exists or not
   *
   * @param   string  $id  id
   * @return  boolean
   * @throws  Cache_Exception
   */
  protected function exists($id)
  {
    $statement = $this->_db->prepare('SELECT id FROM caches WHERE id = :id');
    try
    {
      $statement->execute(array(':id' => $this->_sanitize_id($id)));
    }
    catch (PDOExeption $e)
    {
      throw new Cache_Exception('There was a problem querying the local SQLite3 cache. :error', array(':error' => $e->getMessage()));
    }

    return (bool) $statement->fetchAll();
  }
}
