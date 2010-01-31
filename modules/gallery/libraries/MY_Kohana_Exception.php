<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class Kohana_Exception extends Kohana_Exception_Core {
  /**
   * Dump out the full stack trace as part of the text representation of the exception.
   */
  public static function text($e) {
    return sprintf(
      "%s [ %s ]: %s\n%s [ %s ]\n%s",
      get_class($e), $e->getCode(), strip_tags($e->getMessage()),
      $e->getFile(), $e->getLine(),
      $e->getTraceAsString());
  }

  public static function handle(Exception $e) {
    if ($e instanceof ORM_Validation_Exception) {
      Kohana_Log::add("error", "Validation errors: " . print_r($e->validation->errors(), 1));
    }
    return parent::handle($e);
  }
}