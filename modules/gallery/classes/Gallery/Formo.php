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
class Gallery_Formo extends Formo_Core_Formo {
  /**
   * Add a CSRF element into every form that uses Access::verify_csrf() for validation.
   *
   * @see  Formo::__construct()
   */
  public function __construct(array $array=null) {
    $form = parent::__construct($array);

    // If the driver is form (i.e. the parent form instead of a field within it), add the CSRF.
    if ($form->get("driver") == "form") {
      $form->add("csrf", "input|hidden", Access::csrf_token());
      $form->csrf->add_rule(array("Access::verify_csrf", array()));
    }
    return $form;
  }
}
