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
        $group->delete();
      }
    } catch (Exception $e) { }

    try {
      access::delete_permission("access_test");
    } catch (Exception $e) { }

    try {
      $user = ORM::factory("user")->where("name", "access_test")->find();
      if ($user->loaded) {
        $user->delete();
      }
    } catch (Exception $e) { }
  }

  public function setup() {
    user::set_active(user::guest());
  }

  public function groups_and_permissions_are_bound_to_columns_test() {
    access::register_permission("access_test");
    $group = group::create("access_test");

    // We have a new column for this perm / group combo
    $fields = Database::instance()->list_fields("access_caches");
    $this->assert_true(array_key_exists("access_test_{$group->id}", $fields));

    access::delete_permission("access_test");
    $group->delete();

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
    $intent = ORM::factory("access_intent")->where("item_id", $item)->find();

    // Allow
    access::allow(group::everybody(), "view", $item);
    $this->assert_same(access::ALLOW, $intent->reload()->view_1);

    // Deny
    access::deny(group::everybody(), "view", $item);
    $this->assert_same(
      access::DENY,
      ORM::factory("access_intent")->where("item_id", $item)->find()->view_1);

    // Allow again.  If the initial value was allow, then the first Allow clause above may not
    // have actually changed any values.
    access::allow(group::everybody(), "view", $item);
    $this->assert_same(
      access::ALLOW,
      ORM::factory("access_intent")->where("item_id", $item)->find()->view_1);

    access::reset(group::everybody(), "view", $item);
    $this->assert_same(
      null,
      ORM::factory("access_intent")->where("item_id", $item)->find()->view_1);

    $item->delete();
  }

  public function cant_reset_root_item_test() {
    try {
      access::reset(group::everybody(), "view", ORM::factory("item", 1));
    } catch (Exception $e) {
      return;
    }
    $this->assert_true(false, "Should not be able to reset root intent");
  }

  public function can_view_item_test() {
    $root = ORM::factory("item", 1);
    access::allow(group::everybody(), "view", $root);
    $this->assert_true(access::group_can(group::everybody(), "view", $root));
  }

  public function can_always_fails_on_unloaded_items_test() {
    $root = ORM::factory("item", 1);
    access::allow(group::everybody(), "view", $root);
    $this->assert_true(access::group_can(group::everybody(), "view", $root));

    $bogus = ORM::factory("item", -1);
    $this->assert_false(access::group_can(group::everybody(), "view", $bogus));
  }

  public function cant_view_child_of_hidden_parent_test() {
    $root = ORM::factory("item", 1);
    $album = ORM::factory("item")->add_to_parent($root);
    access::add_item($album);

    access::deny(group::everybody(), "view", $root);
    access::reset(group::everybody(), "view", $album);

    $album->reload();
    $this->assert_false(access::group_can(group::everybody(), "view", $album));
  }

  public function view_permissions_propagate_down_test() {
    $root = ORM::factory("item", 1);
    $album = ORM::factory("item")->add_to_parent($root);
    access::add_item($album);

    access::allow(group::everybody(), "view", $root);
    access::reset(group::everybody(), "view", $album);
    $album->reload();
    $this->assert_true(access::group_can(group::everybody(), "view", $album));
  }

  public function can_toggle_view_permissions_propagate_down_test() {
    $root = ORM::factory("item", 1);
    $album1 = ORM::factory("item");
    $album1->type = "album";
    $album1->add_to_parent($root);
    access::add_item($album1);

    $album2 = ORM::factory("item");
    $album2->type="album";
    $album2->add_to_parent($album1);
    access::add_item($album2);

    $album3 = ORM::factory("item");
    $album3->type="album";
    $album3->add_to_parent($album2);
    access::add_item($album3);

    $album4 = ORM::factory("item");
    $album4->type="album";
    $album4->add_to_parent($album3);
    access::add_item($album4);

    $album1->reload();
    $album2->reload();
    $album3->reload();
    $album4->reload();

    access::allow(group::everybody(), "view", $root);
    access::deny(group::everybody(), "view", $album1);
    access::reset(group::everybody(), "view", $album2);
    access::reset(group::everybody(), "view", $album3);
    access::reset(group::everybody(), "view", $album4);

    $album4->reload();
    $this->assert_false(access::group_can(group::everybody(), "view", $album4));

    access::allow(group::everybody(), "view", $album1);
    $album4->reload();
    $this->assert_true(access::group_can(group::everybody(), "view", $album4));
  }

  public function revoked_view_permissions_cant_be_allowed_lower_down_test() {
    $root = ORM::factory("item", 1);
    $album = ORM::factory("item")->add_to_parent($root);
    access::add_item($album);

    access::deny(group::everybody(), "view", $root);
    access::allow(group::everybody(), "view", $album);

    $album->reload();
    $this->assert_false(access::group_can(group::everybody(), "view", $album));
  }

  public function can_edit_item_test() {
    $root = ORM::factory("item", 1);
    access::allow(group::everybody(), "edit", $root);
    $this->assert_true(access::group_can(group::everybody(), "edit", $root));
  }

  public function non_view_permissions_propagate_down_test() {
    $root = ORM::factory("item", 1);
    $album = ORM::factory("item")->add_to_parent($root);
    access::add_item($album);

    access::allow(group::everybody(), "edit", $root);
    access::reset(group::everybody(), "edit", $album);
    $this->assert_true(access::group_can(group::everybody(), "edit", $album));
  }

  public function non_view_permissions_can_be_revoked_lower_down_test() {
    $root = ORM::factory("item", 1);
    $outer = ORM::factory("item");
    $outer->type = "album";
    $outer->add_to_parent($root);

    access::add_item($outer);
    $outer_photo = ORM::factory("item")->add_to_parent($outer);
    access::add_item($outer_photo);

    $inner = ORM::factory("item");
    $inner->type = "album";
    $inner->add_to_parent($outer);
    access::add_item($inner);
    $inner_photo = ORM::factory("item")->add_to_parent($inner);
    access::add_item($inner_photo);

    $outer->reload();
    $inner->reload();

    access::allow(group::everybody(), "edit", $root);
    access::deny(group::everybody(), "edit", $outer);
    access::allow(group::everybody(), "edit", $inner);

    // Outer album is not editable, inner one is.
    $this->assert_false(access::group_can(group::everybody(), "edit", $outer_photo));
    $this->assert_true(access::group_can(group::everybody(), "edit", $inner_photo));
  }

  public function i_can_edit_test() {
    // Create a new user that belongs to no groups
    $user = user::create("access_test", "Access Test", "");
    foreach ($user->groups as $group) {
      $user->remove($group);
    }
    // @todo remove this reload when http://dev.kohanaphp.com/ticket/959 is resolved
    $user->reload();
    user::set_active($user);

    // This user can't edit anything
    $root = ORM::factory("item", 1);
    $this->assert_false(access::can("edit", $root));

    // Now add them to a group that has edit permission
    $group = group::create("access_test");
    $group->add($user);
    access::allow($group, "edit", $root);
    user::set_active($user->reload());

    // And verify that the user can edit.
    $this->assert_true(access::can("edit", $root));
  }
}
