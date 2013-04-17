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

/**
 * This test case operates under the assumption that Hook_UserInstaller::install() is called by the
 * test controller before it starts.
 */
class User_Installer_Test extends Unittest_Testcase {
  public function install_creates_admin_user_test() {
    $user = ORM::factory("User", 1);
    $this->assert_equal("guest", $user->name);
    $this->assert_true($user->guest);

    $user = ORM::factory("User", 2);
    $this->assert_equal("admin", $user->name);
    $this->assert_false($user->guest);

    $this->assert_equal(
      array("Everybody", "Registered Users"),
      array_keys($user->groups->select_list("name")));
  }

  public function install_creates_everybody_group_test() {
    $group = ORM::factory("Group", 1);
    $this->assert_equal("Everybody", $group->name);
    $this->assert_true($group->special);
  }

  public function install_creates_registered_group_test() {
    $group = ORM::factory("Group", 2);
    $this->assert_equal("Registered Users", $group->name);
  }
}
