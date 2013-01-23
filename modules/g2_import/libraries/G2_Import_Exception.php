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
 * A wrapper for exceptions to report more details in case
 * it's a ORM validation exception.
 */
class G2_Import_Exception extends Exception {
  public function __construct($message, Exception $previous=null, $additional_messages=null) {
    if ($additional_messages) {
      $message .= "\n" . implode("\n", $additional_messages);
    }
    if ($previous && $previous instanceof ORM_Validation_Exception) {
      $message .= "\nORM validation errors: " . print_r($previous->validation->errors(), true);
    }
    if ($previous) {
      $message .= "\n" . (string) $previous;
    }
    // The $previous parameter is supported in PHP 5.3.0+.
    parent::__construct($message);
  }
}