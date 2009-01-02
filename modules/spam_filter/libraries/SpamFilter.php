<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class SpamFilter_Core {

  private static $spam_filter;

  protected $driver;

  public static function instance($driver=null) {
    if (empty(self::$spam_filter)) {
      self::$spam_filter = new SpamFilter($driver);
    }
    return self::$spam_filter;
  }

  protected function __construct($driver=null) {
    if (empty($driver)) {
      $driver = module::get_var("spam_filter", "driver", null);
    }

    // Set driver name
    $driver = "{$driver}_Driver";

    // Load the driver
    if (!Kohana::auto_load($driver)) {
      throw new Exception("@todo SPAM FILTER DRIVER NO FOUND");
    }

    // Initialize the driver
    $this->driver = new $driver();

    // Validate the driver
    if (!($this->driver instanceof SpamFilter_Driver)) {
      throw new Exception("@todo SPAM FILTER DRIVER NOT IMPLEMENTED");
    }
  }

  public function check_comment($comment) {
    $this->_is_initialized();

    $is_valid = $this->driver->check_comment($comment);
    $comment->published = $is_valid;
    return $is_valid;
  }

  public function submit_spam($comment) {
    $this->_is_initialized();

    return $this->driver->submit_spam($comment);
  }

  public function submit_ham($comment) {
    $this->_is_initialized();

    return $this->driver->submit_ham($comment);
  }

  public function get_statistics() {
    $this->_is_initialized();
    return $this->driver->get_statistics();
  }

  public function get_admin_fields($post=null) {
    return $this->driver->get_admin_fields($post);
  }

  public function get_validation_rules($post) {
    $this->driver->get_validation_rules($post);
  }

  public function set_api_data($post) {
    $this->driver->set_api_data($post);
    module::set_var("spam_filter", "key_verified", true);
  }

  private function _is_initialized() {
    $key_verified = module::get_var("spam_filter", "key_verified", null);
    if (empty($key_verified)) {
      throw new Exception("@todo SPAM FILTER NOT INITIALIZED");
    }
  }
}

