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
class User_Model_Group extends ORM implements IdentityProvider_GroupDefinition {
  /**
   * @see ORM::delete()
   */
  public function delete() {
    $old = clone $this;
    Module::event("group_before_delete", $this);
    parent::delete();
    Module::event("group_deleted", $old);

    return $this;
  }

  public function users() {
    return $this->users->find_all()->as_array();
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

  /**
   * Handle any business logic necessary to create a group.
   * @see ORM::create()
   *
   * @return ORM Model_Group
   */
  public function create(Validation $validation=null) {
    Module::event("group_before_create", $this);
    parent::create($validation);
    Module::event("group_created", $this);

    return $this;
  }

  /**
   * Handle any business logic necessary to update a group.
   * @see ORM::update()
   *
   * @return ORM Model_Group
   */
  public function update(Validation $validation=null) {
    Module::event("group_before_update", $this);
    $original = ORM::factory("Group", $this->id);
    parent::update($validation);
    Module::event("group_updated", $original, $this);

    return $this;
  }

  /**
   * Validate the user name.  Make sure there are no conflicts.
   */
  public function valid_name(Validation $v, $field) {
    if (DB::select()->from("groups")
        ->where("name", "=", $this->name)
        ->where("id", "<>", $this->id)
        ->execute()->count() == 1) {
      $v->add_error("name", "conflict");
    }
  }
}
