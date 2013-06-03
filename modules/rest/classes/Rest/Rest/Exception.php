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
class Rest_Rest_Exception extends HTTP_Exception {
  public $message_array;

  /**
   * Similar to HTTP_Exception::factory() except that the message can be an array
   * and the error is independently logged to ease debugging.
   *
   * @see  HTTP_Exception::factory()
   */
  public static function factory($code, $message=null, array $variables=null, Exception $previous=null) {
    $this->message_array = empty($message) ? array() : array("errors" =>
      is_array($message) ? $message : array("other" => $message));

    // Log error response to ease debugging.
    Log::instance()->add(Log::ERROR, "Rest error details: " . print_r($this->message_array, true));

    return parent::factory($code, (string)$message, $variables, $previous);
  }
}
