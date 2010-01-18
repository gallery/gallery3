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
class Kohana_Bug_Test extends Unit_Test_Case {
  function double_save_test_should_fail_test() {
    // http://dev.kohanaframework.org/issues/2504
    $group = ORM::factory("group");
    $group->name = rand();
    $group->save();      // this save works

    try {
      $group->name = null; // now I change to an illegal value
      $group->save();      // this passes, but it shouldn't.  My model is broken!

      // This is the normal state when the bug is not fixed.
    } catch (ORM_Validation_Exception $e) {
      // When this triggers, the bug is fixed.  Find any references to ticket #2504 in the code
      // and update those accordingly
      $this->assert_true(false, "Bug #2504 has been fixed");
    }
  }
}
