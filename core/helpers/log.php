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
  const INFO    = 1;
  const WARNING = 2;
  const ERROR   = 3;

  /**
   * Add a log entry.
   *
   * @param string  $category  an arbitrary category we can use to filter log messages
   * @param string  $message   a detailed log message
   * @param integer $severity  INFO, WARNING or ERROR
   * @param string  $html      an html snippet presented alongside the log message to aid the admin
   */
  function add($category, $message, $severity=INFO, $html) {
    $log = ORM::factory("log");
    $log->category = $category;
    $log->message = $message;
    $log->severity = $severity;
    $log->html = $html;
    $log->url = url::abs_current(true);
    $log->referer = request::referrer(null);
    $log->timestamp = time();
    $log->user_id =  user::active()->id;
    $log->save();
  }
}
