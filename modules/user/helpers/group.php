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
   * @return Group_Model
   */
  static function everybody() {
    return model_cache::get("group", 1);
  }

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @return Group_Model
   */
  static function registered_users() {
    return model_cache::get("group", 2);
  }

  public static function get_edit_form_admin($group) {
    $form = new Forge("admin/groups/edit/$group->id");
    $form_group = $form->group("edit_group")->label(_("Edit Group"));
    $form_group->input("name")->label(_("Name"))->id("gName")->value($group->name);
    $form_group->inputs["name"]->error_messages(
      "in_use", _("There is already a group with that name"));
    $form_group->submit(_("Save"));
    $form->add_rules_from($group);
    return $form;
  }

  public static function get_add_form_admin() {
    $form = new Forge("admin/groups/add");
    $form_group = $form->group("add_group")->label(_("Add Group"));
    $form_group->input("name")->label(_("Name"))->id("gName");
    $form_group->inputs["name"]->error_messages(
      "in_use", _("There is already a group with that name"));
    $form_group->submit(_("Add Group"));
    $group = ORM::factory("group");
    $form->add_rules_from($group);
    return $form;
  }

  public static function get_delete_form_admin($group) {
    $form = new Forge("admin/groups/delete/$group->id", "", "post");
    $form_group = $form->group("delete_group")->label(
      sprintf(_("Are you sure you want to delete group %s?"), $group->name));
    $form_group->submit(_("Delete"));
    return $form;
  }
}
