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
class Access_Helper_Test extends Gallery_Unit_Test_Case {
  private $_group;

  public function setup() {
    identity::set_active_user(identity::guest());
  }

  public function teardown() {
    try {
      $group = identity::lookup_group_by_name("access_test");
      if (!empty($group)) {
        $group->delete();
      }
    } catch (Exception $e) { }

    try {
      access::delete_permission("access_test");
    } catch (Exception $e) { }

    try {
      $user = identity::lookup_user_by_name("access_test");
      if (!empty($user)) {
        $user->delete();
      }
    } catch (Exception $e) { }

    // Reset some permissions that we mangle below
    access::allow(identity::everybody(), "view", item::root());
    identity::set_active_user(identity::admin_user());
  }

  public function groups_and_permissions_are_bound_to_columns_test() {
    access::register_permission("access_test", "Access Test");
    $group = identity::create_group("access_test");

    // We have a new column for this perm / group combo
    $fields = Database::instance()->list_fields("access_caches");
    $this->assert_true(array_key_exists("access_test_{$group->id}", $fields));

    access::delete_permission("access_test");
    $group->delete();

    // Now the column has gone away
    $fields = Database::instance()->list_fields("access_caches");
    $this->assert_false(array_key_exists("access_test_{$group->id}", $fields));
  }

  public function user_can_access_test() {
    $access_test = identity::create_group("access_test");

    access::allow($access_test, "view", item::root());

    $item = test::random_album();

    access::deny(identity::everybody(), "view", $item);
    access::deny(identity::registered_users(), "view", $item);
    $item->reload();

    $user = identity::create_user("access_test", "Access Test", "*****", "user@user.com");
    foreach ($user->groups() as $group) {
      $user->remove($group);
    }
    $user->add($access_test);
    $user->save();

    $this->assert_true(access::user_can($user, "view", $item), "Should be able to view");
  }

  public function user_can_no_access_test() {
    $item = test::random_album();

    access::deny(identity::everybody(), "view", $item);
    access::deny(identity::registered_users(), "view", $item);

    $user = identity::create_user("access_test", "Access Test", "*****", "user@user.com");
    foreach ($user->groups() as $group) {
      $user->remove($group);
    }
    $user->save();

    $this->assert_false(access::user_can($user, "view", $item), "Should be unable to view");
  }

  public function adding_and_removing_items_adds_ands_removes_rows_test() {
    $item = test::random_album();

    // New rows exist
    $this->assert_true(ORM::factory("access_cache")->where("item_id", "=", $item->id)->find()->loaded());
    $this->assert_true(ORM::factory("access_intent")->where("item_id", "=", $item->id)->find()->loaded());

    // Delete the item
    $item->delete();

    // Rows are gone
    $this->assert_false(ORM::factory("access_cache")->where("item_id", "=", $item->id)->find()->loaded());
    $this->assert_false(ORM::factory("access_intent")->where("item_id", "=", $item->id)->find()->loaded());
  }

  public function new_photos_inherit_parent_permissions_test() {
    $album = test::random_album();
    access::allow(identity::everybody(), "view", $album);

    $photo = test::random_photo($album);

    $this->assert_true($photo->__get("view_" . identity::everybody()->id));
  }

  public function can_allow_deny_and_reset_intent_test() {
    $album = test::random_album();
    $intent = ORM::factory("access_intent")->where("item_id", "=", $album->id)->find();

    // Allow
    access::allow(identity::everybody(), "view", $album);
    $this->assert_same(access::ALLOW, $intent->reload()->view_1);

    // Deny
    access::deny(identity::everybody(), "view", $album);
    $this->assert_same(
      access::DENY,
      ORM::factory("access_intent")->where("item_id", "=", $album->id)->find()->view_1);

    // Allow again.  If the initial value was allow, then the first Allow clause above may not
    // have actually changed any values.
    access::allow(identity::everybody(), "view", $album);
    $this->assert_same(
      access::ALLOW,
      ORM::factory("access_intent")->where("item_id", "=", $album->id)->find()->view_1);

    access::reset(identity::everybody(), "view", $album);
    $this->assert_same(
      null,
      ORM::factory("access_intent")->where("item_id", "=", $album->id)->find()->view_1);
  }

  public function cant_reset_root_item_test() {
    try {
      access::reset(identity::everybody(), "view", ORM::factory("item", 1));
    } catch (Exception $e) {
      return;
    }
    $this->assert_true(false, "Should not be able to reset root intent");
  }

  public function can_view_item_test() {
    access::allow(identity::everybody(), "view", item::root());
    $this->assert_true(access::group_can(identity::everybody(), "view", item::root()));
  }

  public function can_always_fails_on_unloaded_items_test() {
    access::allow(identity::everybody(), "view", item::root());
    $this->assert_true(access::group_can(identity::everybody(), "view", item::root()));

    $bogus = ORM::factory("item", -1);
    $this->assert_false(access::group_can(identity::everybody(), "view", $bogus));
  }

  public function cant_view_child_of_hidden_parent_test() {
    $root = item::root();
    $album = test::random_album();

    $root->reload();
    access::deny(identity::everybody(), "view", $root);
    access::reset(identity::everybody(), "view", $album);

    $album->reload();
    $this->assert_false(access::group_can(identity::everybody(), "view", $album));
  }

  public function view_permissions_propagate_down_test() {
    $album = test::random_album();

    access::allow(identity::everybody(), "view", item::root());
    access::reset(identity::everybody(), "view", $album);
    $album->reload();
    $this->assert_true(access::group_can(identity::everybody(), "view", $album));
  }

  public function view_permissions_propagate_down_to_photos_test() {
    $album = test::random_album();
    $photo = test::random_photo($album);
    identity::set_active_user(identity::guest());

    $this->assert_true(access::can("view", $photo));
    $album->reload();  // MPTT pointers have changed, so reload before calling access::deny
    access::deny(identity::everybody(), "view", $album);

    $photo->reload();  // view permissions are cached in the photo, so reload before checking
    $this->assert_false(access::can("view", $photo));
  }

  public function can_toggle_view_permissions_propagate_down_test() {
    $album1 = test::random_album(item::root());
    $album2 = test::random_album($album1);
    $album3 = test::random_album($album2);
    $album4 = test::random_album($album3);

    $album1->reload();
    $album2->reload();
    $album3->reload();
    $album4->reload();

    access::allow(identity::everybody(), "view", item::root());
    access::deny(identity::everybody(), "view", $album1);
    access::reset(identity::everybody(), "view", $album2);
    access::reset(identity::everybody(), "view", $album3);
    access::reset(identity::everybody(), "view", $album4);

    $album4->reload();
    $this->assert_false(access::group_can(identity::everybody(), "view", $album4));

    access::allow(identity::everybody(), "view", $album1);
    $album4->reload();
    $this->assert_true(access::group_can(identity::everybody(), "view", $album4));
  }

  public function revoked_view_permissions_cant_be_allowed_lower_down_test() {
    $root = item::root();
    $album1 = test::random_album($root);
    $album2 = test::random_album($album1);

    $root->reload();
    access::deny(identity::everybody(), "view", $root);
    access::allow(identity::everybody(), "view", $album2);

    $album1->reload();
    $this->assert_false(access::group_can(identity::everybody(), "view", $album1));

    $album2->reload();
    $this->assert_false(access::group_can(identity::everybody(), "view", $album2));
  }

  public function can_edit_item_test() {
    $root = item::root();
    access::allow(identity::everybody(), "edit", $root);
    $this->assert_true(access::group_can(identity::everybody(), "edit", $root));
  }

  public function non_view_permissions_propagate_down_test() {
    $album = test::random_album();

    access::allow(identity::everybody(), "edit", item::root());
    access::reset(identity::everybody(), "edit", $album);
    $this->assert_true(access::group_can(identity::everybody(), "edit", $album));
  }

  public function non_view_permissions_can_be_revoked_lower_down_test() {
    $outer = test::random_album();
    $outer_photo = test::random_photo($outer);

    $inner = test::random_album($outer);
    $inner_photo = test::random_photo($inner);

    $outer->reload();
    $inner->reload();

    access::allow(identity::everybody(), "edit", item::root());
    access::deny(identity::everybody(), "edit", $outer);
    access::allow(identity::everybody(), "edit", $inner);

    // Outer album is not editable, inner one is.
    $this->assert_false(access::group_can(identity::everybody(), "edit", $outer_photo));
    $this->assert_true(access::group_can(identity::everybody(), "edit", $inner_photo));
  }

  public function i_can_edit_test() {
    // Create a new user that belongs to no groups
    $user = identity::create_user("access_test", "Access Test", "*****", "user@user.com");
    foreach ($user->groups() as $group) {
      $user->remove($group);
    }
    $user->save();
    identity::set_active_user($user);

    // This user can't edit anything
    $root = item::root();
    $this->assert_false(access::can("edit", $root));

    // Now add them to a group that has edit permission
    $group = identity::create_group("access_test");
    $group->add($user);
    $group->save();
    access::allow($group, "edit", $root);

    $user = identity::lookup_user($user->id);  // reload() does not flush related columns
    identity::set_active_user($user);

    // And verify that the user can edit.
    $this->assert_true(access::can("edit", $root));
  }

  public function everybody_view_permission_maintains_htaccess_files_test() {
    $album = test::random_album();

    $this->assert_false(file_exists($album->file_path() . "/.htaccess"));

    access::deny(identity::everybody(), "view", $album);
    $this->assert_true(file_exists($album->file_path() . "/.htaccess"));

    access::allow(identity::everybody(), "view", $album);
    $this->assert_false(file_exists($album->file_path() . "/.htaccess"));

    access::deny(identity::everybody(), "view", $album);
    $this->assert_true(file_exists($album->file_path() . "/.htaccess"));

    access::reset(identity::everybody(), "view", $album);
    $this->assert_false(file_exists($album->file_path() . "/.htaccess"));
  }

  public function everybody_view_full_permission_maintains_htaccess_files_test() {
    $album = test::random_album();

    $this->assert_false(file_exists($album->file_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->resize_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->thumb_path() . "/.htaccess"));

    access::deny(identity::everybody(), "view_full", $album);
    $this->assert_true(file_exists($album->file_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->resize_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->thumb_path() . "/.htaccess"));

    access::allow(identity::everybody(), "view_full", $album);
    $this->assert_false(file_exists($album->file_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->resize_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->thumb_path() . "/.htaccess"));

    access::deny(identity::everybody(), "view_full", $album);
    $this->assert_true(file_exists($album->file_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->resize_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->thumb_path() . "/.htaccess"));

    access::reset(identity::everybody(), "view_full", $album);
    $this->assert_false(file_exists($album->file_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->resize_path() . "/.htaccess"));
    $this->assert_false(file_exists($album->thumb_path() . "/.htaccess"));
  }

  public function moved_items_inherit_new_permissions_test() {
    identity::set_active_user(identity::lookup_user_by_name("admin"));

    $public_album = test::random_album();
    $public_photo = test::random_photo($public_album);
    access::allow(identity::everybody(), "view", $public_album);
    access::allow(identity::everybody(), "edit", $public_album);

    item::root()->reload();  // Account for MPTT changes

    $private_album = test::random_album();
    access::deny(identity::everybody(), "view", $private_album);
    access::deny(identity::everybody(), "edit", $private_album);
    $private_photo = test::random_photo($private_album);

    // Make sure that we now have a public photo and private photo.
    $this->assert_true(access::group_can(identity::everybody(), "view", $public_photo));
    $this->assert_false(access::group_can(identity::everybody(), "view", $private_photo));

    // Swap the photos
    item::move($public_photo, $private_album);
    $private_album->reload(); // Reload to get new MPTT pointers and cached perms.
    $public_album->reload();
    $private_photo->reload();
    $public_photo->reload();

    item::move($private_photo, $public_album);
    $private_album->reload(); // Reload to get new MPTT pointers and cached perms.
    $public_album->reload();
    $private_photo->reload();
    $public_photo->reload();

    // Make sure that the public_photo is now private, and the private_photo is now public.
    $this->assert_false(access::group_can(identity::everybody(), "view", $public_photo));
    $this->assert_false(access::group_can(identity::everybody(), "edit", $public_photo));
    $this->assert_true(access::group_can(identity::everybody(), "view", $private_photo));
    $this->assert_true(access::group_can(identity::everybody(), "edit", $private_photo));
  }
}
