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
 * This test case operates under the assumption that core_installer::install() is called by the
 * test controller before it starts.
 */
class User_Installer_Test extends Unit_Test_Case {
  public function install_creates_admin_user_test() {
    $user = ORM::factory("user", 1);
    $this->assert_equal("Gallery Administrator", $user->display_name);
    $this->assert_equal("admin", $user->name);

    $groups = $user->groups->as_array();
    $this->assert_equal(2, count($groups));
    $this->assert_equal("administrator", $groups[0]->name);

    $this->assert_equal("registered", $groups[1]->name);
  }

  public function install_creates_admininstrator_group_test() {
    $group = ORM::factory("group", 1);
    $this->assert_equal("administrator", $group->name);

    $users = $group->users->as_array();
    $this->assert_equal(1, count($users));
    $this->assert_equal("admin", $users[0]->name);
  }

  public function install_creates_registered_group_test() {
    $group = ORM::factory("group", 2);
    $this->assert_equal("registered", $group->name);

    $users = $group->users->as_array();
    $this->assert_equal(1, count($users));
    $this->assert_equal("admin", $users[0]->name);
  }
}
