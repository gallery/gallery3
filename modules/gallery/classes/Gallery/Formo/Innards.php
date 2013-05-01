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
abstract class Gallery_Formo_Innards extends Formo_Core_Innards {
  /**
   * Override Formo_Innards::_error_to_msg() to look for a message in the "error_messages" array.
   * This array can be populated using our Formo::add_rule() override, or by accessing it directly
   * using Formo::set("error_messages",...).  If nothing is found, the error name is returned.
   */
  protected function _error_to_msg(array $errors_array=null) {
    $message = parent::_error_to_msg($errors_array);
    return ($message === false) ? false :
      Arr::get($this->get("error_messages", array()), $message, $message);
  }
}
