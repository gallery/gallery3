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
class User_Model extends ORM {
  protected $has_and_belongs_to_many = array("groups");

  var $rules = array(
    "name" => "length[1,32]",
    "full_name" => "length[0,255]",
    "email" => "valid_email|length[1,255]",
    "password" => "length[1,40]",
    "url" => "valid_url",
    "locale" => "length[2,10]");

  public function __set($column, $value) {
    switch ($column) {
    case "hashed_password":
      $column = "password";
      break;

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
    $old = clone $this;
    module::event("user_before_delete", $this);
    parent::delete($id);
    module::event("user_deleted", $old);
  }

  /**
   * Return a url to the user's avatar image.
   * @param integer $size the target size of the image (default 80px)
   * @return string a url
   */
  public function avatar_url($size=80, $default=null) {
    return sprintf("http://www.gravatar.com/avatar/%s.jpg?s=%d&r=pg%s",
                   md5($this->email), $size, $default ? "&d=" . urlencode($default) : "");
  }

  public function save() {
    if (!$this->loaded) {
        $created = 1;
    }
    parent::save();
    if (isset($created)) {
      module::event("user_created", $this);
    } else {
      module::event("user_updated", $this->original(), $this);
    }
    return $this;
  }

  /**
   * Return the best version of the user's name.  Either their specified full name, or fall back
   * to the user name.
   * @return string
   */
  public function display_name() {
    return empty($this->full_name) ? $this->name : $this->full_name;
  }
}