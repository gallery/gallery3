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
class Gallery_SiteStatus {
  const SUCCESS = 1;
  const INFO    = 2;
  const WARNING = 3;
  const ERROR   = 4;

  /**
   * Report a successful event.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  static function success($msg, $permanent_key) {
    static::_add($msg, static::SUCCESS, $permanent_key);
  }

  /**
   * Report an informational event.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  static function info($msg, $permanent_key) {
    static::_add($msg, static::INFO, $permanent_key);
  }

  /**
   * Report that something went wrong, not fatal, but worth investigation.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  static function warning($msg, $permanent_key) {
    static::_add($msg, static::WARNING, $permanent_key);
  }

  /**
   * Report that something went wrong that should be fixed.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  static function error($msg, $permanent_key) {
    static::_add($msg, static::ERROR, $permanent_key);
  }

  /**
   * Save a message in the session for our next page load.
   * @param string  $msg           a detailed message
   * @param integer $severity      one of the severity constants
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  protected static function _add($msg, $severity, $permanent_key) {
    $message = ORM::factory("Message")
      ->where("key", "=", $permanent_key)
      ->find();
    if (!$message->loaded()) {
      $message->key = $permanent_key;
    }
    $message->severity = $severity;
    $message->value = $msg;
    $message->save();
  }

  /**
   * Remove any permanent message by key.
   * @param string $permanent_key
   */
  static function clear($permanent_key) {
    $message = ORM::factory("Message")->where("key", "=", $permanent_key)->find();
    if ($message->loaded()) {
      $message->delete();
    }
  }

  /**
   * Get any pending messages.  There are two types of messages, transient and permanent.
   * Permanent messages are used to let the admin know that there are pending administrative
   * issues that need to be resolved.  Transient ones are only displayed once.
   * @return HTML text
   */
  static function get() {
    if (!Identity::active_user()->admin) {
      return;
    }
    $buf = array();
    foreach (ORM::factory("Message")->find_all() as $msg) {
      $value = str_replace("__CSRF__", Access::csrf_token(), $msg->value);
      $buf[] = "<li class=\"" . SiteStatus::severity_class($msg->severity) . "\">$value</li>";
    }

    if ($buf) {
      return "<ul id=\"g-site-status\">" . implode("", $buf) . "</ul>";
    }
  }

  /**
   * Convert a message severity to a CSS class
   * @param  integer $severity
   * @return string
   */
  static function severity_class($severity) {
    switch($severity) {
    case SiteStatus::SUCCESS:
      return "g-success";

    case SiteStatus::INFO:
      return "g-info";

    case SiteStatus::WARNING:
      return "g-warning";

    case SiteStatus::ERROR:
      return "g-error";
    }
  }
}
