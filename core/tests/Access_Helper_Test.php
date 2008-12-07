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
  private $_group;

  public function teardown() {
    try {
      $group = ORM::factory("group")->where("name", "access_test")->find();
      if ($group->loaded) {
        group::delete($group->id);
      }
    } catch (Exception $e) { }

    try {
      access::delete_permission("access_test");
    } catch (Exception $e) { }
  }

  public function groups_and_permissions_are_bound_to_columns_test() {
    access::register_permission("access_test");
    $group = group::create("access_test");

    // We have a new column for this perm / group combo
    $fields = Database::instance()->list_fields("access_caches");
    $this->assert_true(array_key_exists("access_test_{$group->id}", $fields));

    access::delete_permission("access_test");
    group::delete($group->id);

    // Now the column has gone away
    $fields = Database::instance()->list_fields("access_caches");
    $this->assert_false(array_key_exists("access_test_{$group->id}", $fields));
  }

  public function adding_and_removing_items_adds_ands_removes_rows_test() {
    $root = ORM::factory("item", 1);
    $item = ORM::factory("item")->add_to_parent($root);

    // Simulate an event
    access::add_item($item);

    // New rows exist
    $this->assert_true(ORM::factory("access_cache")->where("item_id", $item->id)->find()->loaded);
    $this->assert_true(ORM::factory("access_intent")->where("item_id", $item->id)->find()->loaded);

    // Simulate a delete event
    access::delete_item($item);

    // Rows are gone
    $this->assert_false(ORM::factory("access_cache")->where("item_id", $item->id)->find()->loaded);
    $this->assert_false(ORM::factory("access_intent")->where("item_id", $item->id)->find()->loaded);

    $item->delete();
  }

  public function can_allow_deny_and_reset_intent_test() {
    $root = ORM::factory("item", 1);
    $item = ORM::factory("item")->add_to_parent($root);
    access::add_item($item);
    $intent = ORM::factory("access_intent")->where("item_id", $item->id)->find();

    // Allow
    access::allow(0, "view", $item->id);
    $this->assert_same(access::ALLOW, $intent->reload()->view_0);

    // Deny
    access::deny(0, "view", $item->id);
    $this->assert_same(
      access::DENY,
      ORM::factory("access_intent")->where("item_id", $item->id)->find()->view_0);

    // Allow again.  If the initial value was allow, then the first Allow clause above may not
    // have actually changed any values.
    access::allow(0, "view", $item->id);
    $this->assert_same(
      access::ALLOW,
      ORM::factory("access_intent")->where("item_id", $item->id)->find()->view_0);

    access::reset(0, "view", $item->id);
    $this->assert_same(
      null,
      ORM::factory("access_intent")->where("item_id", $item->id)->find()->view_0);

    $item->delete();
  }

  public function cant_reset_root_item_test() {
    try {
      access::reset(0, "view", 1);
    } catch (Exception $e) {
      return;
    }
    $this->assert_true(false, "Should not be able to reset root intent");
  }


  public function can_view_item_test() {
  }

  public function cant_view_child_of_hidden_parent_test() {
  }

  public function view_permissions_propagate_down_test() {
  }

  public function revoked_view_permissions_cant_be_allowed_lower_down_test() {
  }

  public function can_edit_item_test() {
  }

  public function non_view_permissions_propagate_down_test() {
  }

  public function non_view_permissions_can_be_revoked_lower_down_test() {
  }
}
