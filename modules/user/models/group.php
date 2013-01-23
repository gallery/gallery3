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
class Group_Model_Core extends ORM implements Group_Definition {
  protected $has_and_belongs_to_many = array("users");
  protected $users_cache = null;

  /**
   * @see ORM::delete()
   */
  public function delete($id=null) {
    $old = clone $this;
    module::event("group_before_delete", $this);
    parent::delete($id);

    db::build()
      ->delete("groups_users")
      ->where("group_id", "=", empty($id) ? $old->id : $id)
      ->execute();

    module::event("group_deleted", $old);
    $this->users_cache = null;
  }

  public function users() {
    if (!$this->users_cache) {
      $this->users_cache = $this->users->find_all()->as_array();
    }
    return $this->users_cache;
  }

  /**
   * Specify our rules here so that we have access to the instance of this model.
   */
  public function validate(Validation $array=null) {
    // validate() is recursive, only modify the rules on the outermost call.
    if (!$array) {
      $this->rules = array(
        "name" => array("rules" => array("required", "length[1,255]"),
                        "callbacks" => array(array($this, "valid_name"))));
    }

    parent::validate($array);
  }

  public function save() {
    if (!$this->loaded()) {
      // New group
      parent::save();
      module::event("group_created", $this);
    } else {
      // Updated group
      $original = ORM::factory("group", $this->id);
      parent::save();
      module::event("group_updated", $original, $this);
    }

    $this->users_cache = null;
    return $this;
  }

  /**
   * Validate the user name.  Make sure there are no conflicts.
   */
  public function valid_name(Validation $v, $field) {
    if (db::build()->from("groups")
        ->where("name", "=", $this->name)
        ->where("id", "<>", $this->id)
        ->count_records() == 1) {
      $v->add_error("name", "conflict");
    }
  }
}