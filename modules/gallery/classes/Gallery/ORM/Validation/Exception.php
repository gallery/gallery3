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
class Gallery_ORM_Validation_Exception extends Kohana_ORM_Validation_Exception {
  /**
   * Overload ORM_Validation_Exception::__construct() to change the default message
   * from "Failed to validate array" to something more instructive.
   * @see  ORM::__construct().
   */
  public function __construct($alias, Validation $object, $message=null, array $values=null, $code=0, Exception $previous=null) {
    if (empty($message)) {
      $message = "ORM errors for $alias #{$object['id']}:";
      foreach ($object->errors() as $key => $val) {
        $message .= " $key failed $val[0]";
        if (!empty($val[1])) {
          $message .= " (" . implode(", ", $val[1]) . ")";
        }
        $message .= ",";
      }
      $message = substr($message, 0, -1) . ".";
    }

    parent::__construct($alias, $object, $message, $values, $code, $previous);
  }
}
