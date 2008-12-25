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

/**
 * This is the API for handling groups.
 *
 * Note: by design, this class does not do any permission checking.
 */
class group_Core {
  /**
   * Create a new group.
   *
   * @param string  $name
   * @return Group_Model
   */
  static function create($name) {
    $group = ORM::factory("group")->where("name", $name)->find();
    if ($group->loaded) {
      throw new Exception("@todo GROUP_ALREADY_EXISTS $name");
    }

    $group->name = $name;
    $group->save();

    module::event("group_created", $group);
    return $group;
  }

  /**
   * The group of all possible visitors.  This includes the guest user.
   *
   * @todo consider caching
   *
   * @return Group_Model
   */
  static function everybody() {
    return ORM::factory("group", 1);
  }

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @todo consider caching
   *
   * @return Group_Model
   */
  static function registered_users() {
    return ORM::factory("group", 2);
  }
  
  /**
   * This is the API for handling groups.
   * @TODO incorporate rules!
   */
  public static function get_edit_form($group, $action = NULL) {
    $form = new Forge($action);
    $form_group = $form->group("edit_group")->label(_("Edit Group"));
    $form_group->input("gname")->label(_("Name"))->id("gName")->value($group->name);
    $form_group->submit(_("Modify"));
    $form->add_rules_from($group);
    $form->edit_group->gname->rules($group->rules["name"]);
    return $form;
  }
  
  public static function get_add_form($action = NULL) {
    $form = new Forge($action);
    $form_group = $form->group("add_group")->label(_("Add Group"));
    $form_group->input("gname")->label(_("Name"))->id("gName");
    $form_group->submit(_("Create"));
    $group = ORM::factory("group");
    $form->add_rules_from($group);
    $form->add_group->gname->rules($group->rules["name"]);
    return $form;
  }
  
  public static function get_delete_form($group, $action = NULL) {
    $form = new Forge($action);
    $form_group = $form->group("delete_group")->label(_("Delete Group"));
    $form_group->label(sprintf(_("Are you sure you want to delete %s?"), $group->name));
    $form_group->submit(_("Delete"));
    return $form;
  }
}