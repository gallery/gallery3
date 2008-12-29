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
class Admin_Groups_Controller extends Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_groups.html");
    $view->content->groups = ORM::factory("group")->orderby("name")->find_all();
    print $view;
  }

  public function add() {
    $form = group::get_add_form_admin();
    if($form->validate()) {
      $group = group::create($form->add_group->inputs["name"]->value);
      $group->save();
      message::success(sprintf(_("Created group %s"), $group->name));
      print json_encode(array("result" => "success"));
    } else {
      message::error(_("Failed to create group"));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function add_form() {
    print group::get_add_form_admin();
  }
  
  public function delete($id) {
    $group = ORM::factory("group", $id);
    if (!$group->loaded) {
      kohana::show_404();
    }

    $form = group::get_delete_form_admin($group);
    if($form->validate()) {
      $name = $group->name;
      $group->delete();
    } else {
      message::error(_("Failed to delete group"));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }

    log::success("group", sprintf(_("Deleted group %s"), $name));
    message::success(sprintf(_("Deleted group %s"), $name));
    print json_encode(array("result" => "success"));
  }

  public function delete_form($id) {
    $group = ORM::factory("group", $id);
    if (!$group->loaded) {
      kohana::show_404();
    }
    print group::get_delete_form_admin($group);
  }
  
  public function edit($id) {
    $group = ORM::factory("group", $id);
    if (!$group->loaded) {
      kohana::show_404();
    }

    $form = group::get_edit_form_admin($group);
    if($form->validate()) {
      $group->name = $form->edit_group->inputs["name"]->value;
      $group->save();
      message::success(sprintf(_("Changed group %s"), $group->name));
      print json_encode(array("result" => "success"));
    } else {
      message::error(sprintf(_("Failed to change group %s"), $group->name));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }
  
  public function edit_form($id) {
    $group = ORM::factory("group", $id);
    if (!$group->loaded) {
      kohana::show_404();
    }

    print group::get_edit_form_admin($group);
  }
}
