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
class Access_Helper_Test extends Unit_Test_Case {
  public function can_view_item_test() {
  }

  public function cant_view_child_of_hidden_parent_test() {
  }

  public function view_permissions_propagate_down_test() {
  }

  public function revoked_view_permissions_cant_be_allowed_lower_down_test() {
  }

  public function can_reset_intent_test() {
  }

  public function can_edit_item_test() {
  }

  public function cant_reset_root_item_test() {
  }

  public function non_view_permissions_propagate_down_test() {
  }

  public function non_view_permissions_can_be_revoked_lower_down_test() {
  }

  public function new_groups_and_permissions_add_columns_test() {
  }

  public function deleting_groups_and_permissions_removes_columns_test() {
  }

  public function adding_items_adds_rows_test() {
  }

  public function removing_items_remove_rows_test() {
  }
}
