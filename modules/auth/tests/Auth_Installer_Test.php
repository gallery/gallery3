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
class Auth_Installer_Test extends Unit_Test_Case {
  public function install_basic_add_password_test() {
    $user = ORM::factory('user')->find(1);

    $auth = Auth::instance(array('driver' => 'Basic'));

    $auth->set_user_password($user->id, "test_password");

    $this->assert_false($auth->is_valid_password($user->id, "invalid_password"));
    $this->assert_true($auth->is_valid_password($user->id, "test_password"));
  }
}
