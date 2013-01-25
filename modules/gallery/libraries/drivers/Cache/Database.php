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
/*
 * Based on the Cache_Sqlite_Driver developed by the Kohana Team
 */
class Cache_Database_Driver extends Cache_Driver {
  // Kohana database instance
  protected $db;

  /**
   * Sets a cache item to the given data, tags, and lifetime.
   *
   * @param   array    assoc array of key => value pairs
   * @param   array    cache tags
   * @param   integer  lifetime
   * @return  bool
   */
  public function set($items, $tags=null, $lifetime=null) {
    if (!empty($tags)) {
      // Escape the tags, adding brackets so the tag can be explicitly matched
      $tags = "<" . implode(">,<", $tags) . ">";
    } else {
      $tags = null;
    }

    // Cache Database driver expects unix timestamp
    if ($lifetime !== 0) {
      $lifetime += time();
    }

    $db = Database::instance();
    $tags = $db->escape($tags);
    foreach ($items as $id => $data) {
      $id = $db->escape($id);
      $data = $db->escape(serialize($data));
      $db->query("INSERT INTO {caches} (`key`, `tags`, `expiration`, `cache`)
                  VALUES ('$id', '$tags', $lifetime, '$data')
                  ON DUPLICATE KEY UPDATE `tags` = VALUES(tags), `expiration` = VALUES(expiration),
                  `cache` = VALUES(cache)");
    }

    return true;
  }

  /**
   * Get cache items by tag
   * @param   array    cache tags
   * @return  array    cached data
   */
  public function get_tag($tags) {
    $db = db::build()
      ->select()
      ->from("caches");
    foreach ($tags as $tag) {
      $db->where("tags", "LIKE", "%" . Database::escape_for_like("<$tag>") . "%");
    }
    $db_result = $db->execute();

    // An array will always be returned
    $result = array();

    // Disable notices for unserializing
    $ER = error_reporting(~E_NOTICE);
    if ($db_result->count() > 0) {
      foreach ($db_result as $row) {
        // Add each cache to the array
        $result[$row->key] = unserialize($row->cache);
      }
    }
    error_reporting($ER);

    return $result;
  }

  /**
   * Fetches a cache item. This will delete the item if it is expired or if
   * the hash does not match the stored hash.
   *
   * @param  string  cache id
   * @return mixed|NULL
   */
  public function get($keys, $single=false) {
    $data = null;
    $result = db::build()
      ->select()
      ->from("caches")
      ->where("key", "IN", $keys)
      ->execute();

    if (count($result) > 0) {
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
   * Deletes a cache item by id or tag
   *
   * @param  string  cache id or tag, or true for "all items"
   * @param  bool    delete a tag
   * @return bool
   */
  public function delete($keys, $is_tag=false) {
    $db = db::build()
      ->delete("caches");
    if ($keys === true) {
      // Delete all caches
    } else if ($is_tag === true) {
      foreach ($keys as $tag) {
        $db->where("tags", "LIKE", "%" . Database::escape_for_like("<$tag>") . "%");
      }
    } else {
      $db->where("key", "IN", $keys);
    }

    $status = $db->execute();

    return count($status) > 0;
  }

  /**
   * Delete cache items by tag
   */
  public function delete_tag($tags) {
    return $this->delete($tags, true);
  }

  /**
   * Empty the cache
   */
  public function delete_all() {
    Database::instance()->query("TRUNCATE {caches}");
  }
}