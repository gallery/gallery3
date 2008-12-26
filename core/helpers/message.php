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
class message_Core {
  const SUCCESS = 1;
  const INFO    = 2;
  const WARNING = 3;
  const ERROR   = 4;

  /**
   * Report a successful event.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  public static function success($msg, $permanent_key=null) {
    self::add($msg, self::SUCCESS, $permanent_key);
  }

  /**
   * Report an informational event.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  public static function info($msg, $permanent_key=null) {
    self::add($msg, self::INFO, $permanent_key);
  }

  /**
   * Report that something went wrong, not fatal, but worth investigation.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  public static function warning($msg, $permanent_key=null) {
    self::add($msg, self::WARNING, $permanent_key);
  }

  /**
   * Report that something went wrong that should be fixed.
   * @param string  $msg           a detailed message
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  public static function error($msg, $permanent_key=null) {
    self::add($msg, self::ERROR, $permanent_key);
  }

  /**
   * Save a message in the session for our next page load.
   * @param string  $msg           a detailed message
   * @param integer $severity      one of the severity constants
   * @param string  $permanent_key make this message permanent and store it under this key
   */
  private function add($msg, $severity, $permanent_key=null) {
    if ($permanent_key) {
      $message = ORM::factory("message")
        ->where("key", $permanent_key)
        ->find();
      if (!$message->loaded) {
        $message->key = $permanent_key;
      }
      $message->severity = $severity;
      $message->value = $msg;
      $message->save();
    } else {
      $session = Session::instance();
      $status = $session->get("messages");
      $status[] = array($msg, $severity);
      $session->set("messages", $status);
    }
  }

  /**
   * Get any pending messages.  There are two types of messages, transient and permanent.
   * Permanent messages are used to let the admin know that there are pending administrative
   * issues that need to be resolved.  Transient ones are only displayed once.
   * @return html text
   */
  public function get() {
    $buf = array();

    foreach (Session::instance()->get_once("messages", array()) as $msg) {
      $buf[] = "<li class=\"" . self::severity_class($msg[1]) . "\">$msg[0]</li>";
    }

    if (user::active()->admin) {
      foreach (ORM::factory("message")->find_all() as $msg) {
        $buf[] = "<li class=\"" . self::severity_class($msg->severity) . "\">$msg->value</li>";
      }
    }

    if ($buf) {
      return "<ul id=\"gMessages\">" . implode("", $buf) . "</ul>";
    }
  }

  /**
   * Convert a message severity to a CSS class
   * @param  integer $severity
   * @return string
   */
  public function severity_class($severity) {
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
