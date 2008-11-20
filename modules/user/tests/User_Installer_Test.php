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
 * This test case operates under the assumption that user_installer::install() is called by the
 * test controller before it starts.
 */
class User_Installer_Test extends Unit_Test_Case {
  public function install_creates_admin_user_test() {
    $user = ORM::factory("user", 1);
    $this->assert_equal("Gallery Administrator", $user->display_name);
    $this->assert_equal("admin", $user->name);
    $this->assert_true(user::is_correct_password($user, "admin"));

    $this->assert_equal(
      array("administrator", "registered"),
      array_keys($user->groups->select_list("name")));
  }

  public function install_creates_admininstrator_group_test() {
    $group = ORM::factory("group", 1);
    $this->assert_equal("administrator", $group->name);

    $this->assert_equal(
      array("admin"),
      array_keys($group->users->select_list("name")));
  }

  public function install_creates_registered_group_test() {
    $group = ORM::factory("group", 2);
    $this->assert_equal("registered", $group->name);

    $this->assert_equal(
      array("admin", "joe"),
      array_keys($group->users->select_list("name")));
  }
}
