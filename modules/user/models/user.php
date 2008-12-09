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
class User_Model extends ORM {
  protected $has_and_belongs_to_many = array("groups");

  var $rules = array(
    "name" => "required|length[4,32]",
    "display_name" => "length[0,255]",
    "email" => "valid_email|length[4,255]",
    "password" => "required|length[5,40]");

  public function __set($column, $value) {
    switch ($column) {
      case "password":
        $value = user::hash_password($value);
        break;
    }
    parent::__set($column, $value);
  }

  /**
   * @see ORM::delete()
   */
  public function delete($id=null) {
    module::event("user_before_delete", $this);
    parent::delete($id);
  }
}