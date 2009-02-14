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
class log_Core {
  const SUCCESS = 1;
  const INFO    = 2;
  const WARNING = 3;
  const ERROR   = 4;

  /**
   * Report a successful event.
   * @param string  $category  an arbitrary category we can use to filter log messages
   * @param string  $message   a detailed log message
   * @param string  $html      an html snippet presented alongside the log message to aid the admin
   */
  static function success($category, $message, $html="") {
    self::_add($category, $message, $html, self::SUCCESS);
  }

  /**
   * Report an informational event.
   * @param string  $category  an arbitrary category we can use to filter log messages
   * @param string  $message   a detailed log message
   * @param string  $html      an html snippet presented alongside the log message to aid the admin
   */
  static function info($category, $message, $html="") {
    self::_add($category, $message, $html, self::INFO);
  }

  /**
   * Report that something went wrong, not fatal, but worth investigation.
   * @param string  $category  an arbitrary category we can use to filter log messages
   * @param string  $message   a detailed log message
   * @param string  $html      an html snippet presented alongside the log message to aid the admin
   */
  static function warning($category, $message, $html="") {
    self::_add($category, $message, $html, self::WARNING);
  }

  /**
   * Report that something went wrong that should be fixed.
   * @param string  $category  an arbitrary category we can use to filter log messages
   * @param string  $message   a detailed log message
   * @param string  $html      an html snippet presented alongside the log message to aid the admin
   */
  static function error($category, $message, $html="") {
    self::_add($category, $message, $html, self::ERROR);
  }

  /**
   * Add a log entry.
   *
   * @param string  $category  an arbitrary category we can use to filter log messages
   * @param string  $message   a detailed log message
   * @param integer $severity  INFO, WARNING or ERROR
   * @param string  $html      an html snippet presented alongside the log message to aid the admin
   */
  private static function _add($category, $message, $html, $severity) {
    $log = ORM::factory("log");
    $log->category = $category;
    $log->message = $message;
    $log->severity = $severity;
    $log->html = $html;
    $log->url = substr(url::abs_current(true), 0, 255);
    $log->referer = request::referrer(null);
    $log->timestamp = time();
    if (module::is_installed("user")) {
      $log->user_id = user::active()->id;
    }
    $log->save();
  }


  /**
   * Convert a message severity to a CSS class
   * @param  integer $severity
   * @return string
   */
  static function severity_class($severity) {
    switch($severity) {
    case self::SUCCESS:
      return "gSuccess";

    case self::INFO:
      return "gInfo";

    case self::WARNING:
      return "gWarning";

    case self::ERROR:
      return "gError";
    }
  }
}
