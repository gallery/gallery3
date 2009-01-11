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

class User_Groups_Test extends Unit_Test_Case {
  public function teardown() {
    try {
      $group = ORM::factory("group")->where("name", "user_groups_test")->find();
      if ($group->loaded) {
        $group->delete();
      }
    } catch (Exception $e) { }

    try {
      $user = ORM::factory("user")->where("name", "user_groups_test")->find();
      if ($user->loaded) {
        $user->delete();
      }
    } catch (Exception $e) { }
  }

  public function add_user_to_group_test() {
    $user = ORM::factory("user");
    $user->name = "user_groups_test";
    $user->full_name = "user groups test";
    $user->password = "test password";
    $user->save();

    $group = ORM::factory("group");
    $group->name = "user_groups_test";
    $group->save();

    $group->add($user);
    $group->save();

    $this->assert_true($user->has($group));
  }
}
