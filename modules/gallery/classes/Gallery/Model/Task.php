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
class Task_Model_Core extends ORM {
  public function get($key, $default=null) {
    $context = unserialize($this->context);
    if (array_key_exists($key, $context)) {
      return $context[$key];
    } else {
      return $default;
    }
  }

  public function set($key, $value=null) {
    $context = unserialize($this->context);
    $context[$key] = $value;
    $this->context = serialize($context);
  }

  public function save() {
    if (!empty($this->changed)) {
      $this->updated = time();
    }
    return parent::save();
  }

  public function delete($ignored_id=null) {
    Cache::instance()->delete($this->_cache_key());
    return parent::delete();
  }

  public function owner() {
    return identity::lookup_user($this->owner_id);
  }

  /**
   * Log a message to the task log.
   * @params $msg mixed a string or array of strings
   */
  public function log($msg) {
    $key = $this->_cache_key();
    $log = Cache::instance()->get($key);

    if (is_array($msg)) {
      $msg = implode("\n", $msg);
    }

    // Save for 30 days.
    $log .= !empty($log) ? "\n" : "";
    Cache::instance()->set($key, "$log{$msg}",
                           array("task", "log", "import"), 2592000);
  }

  /**
   * Retrieve the cached log information for this task.
   * @returns the log data or null if there is no log data
   */
  public function get_log() {
    $log_data = Cache::instance()->get($this->_cache_key());
    return  $log_data !== null ? $log_data : false;
  }

  /**
   * Build the task cache key
   * @returns the key to use in access the cache
   */
  private function _cache_key() {
    return md5("$this->id; $this->name; $this->callback");
  }
}