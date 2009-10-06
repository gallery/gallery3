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

/**
 * This is the API for handling groups.
 *
 * Note: by design, this class does not do any permission checking.
 */
class group_Core {
  static function get_edit_form_admin($group) {
    $form = new Forge("admin/users/edit_group/$group->id", "", "post", array("id" => "g-edit-group-form"));
    $form_group = $form->group("edit_group")->label(t("Edit Group"));
    $form_group->input("name")->label(t("Name"))->id("g-name")->value($group->name);
    $form_group->inputs["name"]->error_messages(
      "in_use", t("There is already a group with that name"));
    $form_group->submit("")->value(t("Save"));
    $form->add_rules_from(self::get_edit_rules());
    return $form;
  }

  static function get_add_form_admin() {
    $form = new Forge("admin/users/add_group", "", "post", array("id" => "g-add-group-form"));
    $form->set_attr('class', "g-narrow");
    $form_group = $form->group("add_group")->label(t("Add Group"));
    $form_group->input("name")->label(t("Name"))->id("g-name");
    $form_group->inputs["name"]->error_messages(
      "in_use", t("There is already a group with that name"));
    $form_group->submit("")->value(t("Add Group"));
    $group = ORM::factory("group");
    $form->add_rules_from(self::get_edit_rules());
    return $form;
  }

  static function get_delete_form_admin($group) {
    $form = new Forge("admin/users/delete_group/$group->id", "", "post",
                      array("id" => "g-delete-group-form"));
    $form_group = $form->group("delete_group")->label(
      t("Are you sure you want to delete group %group_name?", array("group_name" => $group->name)));
    $form_group->submit("")->value(t("Delete"));
    return $form;
  }

  /**
   * Create a new group.
   *
   * @param string  $name
   * @return Group_Core
   */
  static function create($name) {
    return Identity::instance()->create_group($name);
  }

  /**
   * The group of all possible visitors.  This includes the guest user.
   *
   * @return Group_Core
   */
  static function everybody() {
    return Identity::instance()->everybody();
  }

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @return Group_Core
   */
  static function registered_users() {
    return Identity::instance()->everybody();
  }

  /**
   * Look up a group by id.
   * @param integer      $id the user id
   * @return Group_Model  the group object, or null if the id was invalid.
   */
  static function lookup($id) {
    return Identity::instance()->lookup_group($id);
  }

  /**
   * Look up a group by name.
   * @param integer      $id the group name
   * @return Group_Core  the group object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return Identity::instance()->lookup_group_by_name($name);
  }

  /**
   * List the groups
   * @param mixed      options to apply to the selection of the user
   * @return array     the group list.
   */
  static function groups($filter=array()) {
    return Identity::instance()->list_groups($filter);
  }

  /**
   * Return the edit rules associated with an group.
   *
   * @return stdClass containing the rules
   */
  static function get_edit_rules() {
    return Identity::instance()->get_edit_rules("group");
  }
}
