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
class Access_Helper_Test extends Unittest_TestCase {
  private $_group;

  public function setup() {
    parent::setup();
    Identity::set_active_user(Identity::guest());
  }

  public function teardown() {
    try {
      $group = Identity::lookup_group_by_name("access_test");
      if (!empty($group)) {
        $group->delete();
      }
    } catch (Exception $e) { }

    try {
      Access::delete_permission("access_test");
    } catch (Exception $e) { }

    try {
      $user = Identity::lookup_user_by_name("access_test");
      if (!empty($user)) {
        $user->delete();
      }
    } catch (Exception $e) { }

    // Reset some permissions that we mangle below
    Access::allow(Identity::everybody(), "view", Item::root());
    Identity::set_active_user(Identity::admin_user());
    parent::teardown();
  }

  public function test_groups_and_permissions_are_bound_to_columns() {
    Access::register_permission("access_test", "Access Test");
    $group = Identity::create_group("access_test");

    // We have a new column for this perm / group combo
    $fields = Database::instance()->list_columns("access_caches");
    $this->assertTrue(array_key_exists("access_test_{$group->id}", $fields));

    Access::delete_permission("access_test");
    $group->delete();

    // Now the column has gone away
    $fields = Database::instance()->list_columns("access_caches");
    $this->assertFalse(array_key_exists("access_test_{$group->id}", $fields));
  }

  public function test_user_can_access() {
    $access_test = Identity::create_group("access_test");

    Access::allow($access_test, "view", Item::root());

    $item = Test::random_album();

    Access::deny(Identity::everybody(), "view", $item);
    Access::deny(Identity::registered_users(), "view", $item);
    $item->reload();

    $user = Identity::create_user("access_test", "Access Test", "*****", "user@user.com");
    foreach ($user->groups() as $group) {
      $user->remove("groups", $group);
    }
    $user->add("groups", $access_test);
    $user->save();

    $this->assertTrue(Access::user_can($user, "view", $item), "Should be able to view");
  }

  public function test_user_can_no_access() {
    $item = Test::random_album();

    Access::deny(Identity::everybody(), "view", $item);
    Access::deny(Identity::registered_users(), "view", $item);

    $user = Identity::create_user("access_test", "Access Test", "*****", "user@user.com");
    foreach ($user->groups() as $group) {
      $user->remove("groups", $group);
    }
    $user->save();

    $this->assertFalse(Access::user_can($user, "view", $item), "Should be unable to view");
  }

  public function test_adding_and_removing_items_adds_ands_removes_rows() {
    $item = Test::random_album();

    // New rows exist
    $this->assertTrue(ORM::factory("AccessCache")->where("item_id", "=", $item->id)->find()->loaded());
    $this->assertTrue(ORM::factory("AccessIntent")->where("item_id", "=", $item->id)->find()->loaded());

    // Delete the item
    $item->delete();

    // Rows are gone
    $this->assertFalse(ORM::factory("AccessCache")->where("item_id", "=", $item->id)->find()->loaded());
    $this->assertFalse(ORM::factory("AccessIntent")->where("item_id", "=", $item->id)->find()->loaded());
  }

  public function test_new_photos_inherit_parent_permissions() {
    $album = Test::random_album();
    Access::allow(Identity::everybody(), "view", $album);

    $photo = Test::random_photo($album);

    $this->assertTrue($photo->__get("view_" . Identity::everybody()->id));
  }

  public function test_can_allow_deny_and_reset_intent() {
    $album = Test::random_album();
    $intent = ORM::factory("AccessIntent")->where("item_id", "=", $album->id)->find();

    // Allow
    Access::allow(Identity::everybody(), "view", $album);
    $this->assertSame(Access::ALLOW, $intent->reload()->view_1);

    // Deny
    Access::deny(Identity::everybody(), "view", $album);
    $this->assertSame(
      Access::DENY,
      ORM::factory("AccessIntent")->where("item_id", "=", $album->id)->find()->view_1);

    // Allow again.  If the initial value was allow, then the first Allow clause above may not
    // have actually changed any values.
    Access::allow(Identity::everybody(), "view", $album);
    $this->assertSame(
      Access::ALLOW,
      ORM::factory("AccessIntent")->where("item_id", "=", $album->id)->find()->view_1);

    Access::reset(Identity::everybody(), "view", $album);
    $this->assertSame(
      null,
      ORM::factory("AccessIntent")->where("item_id", "=", $album->id)->find()->view_1);
  }

  public function test_cant_reset_root_item() {
    try {
      Access::reset(Identity::everybody(), "view", ORM::factory("Item", 1));
    } catch (Exception $e) {
      return;
    }
    $this->assertTrue(false, "Should not be able to reset root intent");
  }

  public function test_can_view_item() {
    Access::allow(Identity::everybody(), "view", Item::root());
    $this->assertTrue(Access::group_can(Identity::everybody(), "view", Item::root()));
  }

  public function test_can_always_fails_on_unloaded_items() {
    Access::allow(Identity::everybody(), "view", Item::root());
    $this->assertTrue(Access::group_can(Identity::everybody(), "view", Item::root()));

    $bogus = ORM::factory("Item", -1);
    $this->assertFalse(Access::group_can(Identity::everybody(), "view", $bogus));
  }

  public function test_cant_view_child_of_hidden_parent() {
    $root = Item::root();
    $album = Test::random_album();

    $root->reload();
    Access::deny(Identity::everybody(), "view", $root);
    Access::reset(Identity::everybody(), "view", $album);

    $album->reload();
    $this->assertFalse(Access::group_can(Identity::everybody(), "view", $album));
  }

  public function test_view_permissions_propagate_down() {
    $album = Test::random_album();

    Access::allow(Identity::everybody(), "view", Item::root());
    Access::reset(Identity::everybody(), "view", $album);
    $album->reload();
    $this->assertTrue(Access::group_can(Identity::everybody(), "view", $album));
  }

  public function test_view_permissions_propagate_down_to_photos() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    Identity::set_active_user(Identity::guest());

    $this->assertTrue(Access::can("view", $photo));
    $album->reload();  // MPTT pointers have changed, so reload before calling Access::deny
    Access::deny(Identity::everybody(), "view", $album);

    $photo->reload();  // view permissions are cached in the photo, so reload before checking
    $this->assertFalse(Access::can("view", $photo));
  }

  public function test_can_toggle_view_permissions_propagate_down() {
    $album1 = Test::random_album(Item::root());
    $album2 = Test::random_album($album1);
    $album3 = Test::random_album($album2);
    $album4 = Test::random_album($album3);

    $album1->reload();
    $album2->reload();
    $album3->reload();
    $album4->reload();

    Access::allow(Identity::everybody(), "view", Item::root());
    Access::deny(Identity::everybody(), "view", $album1);
    Access::reset(Identity::everybody(), "view", $album2);
    Access::reset(Identity::everybody(), "view", $album3);
    Access::reset(Identity::everybody(), "view", $album4);

    $album4->reload();
    $this->assertFalse(Access::group_can(Identity::everybody(), "view", $album4));

    Access::allow(Identity::everybody(), "view", $album1);
    $album4->reload();
    $this->assertTrue(Access::group_can(Identity::everybody(), "view", $album4));
  }

  public function test_revoked_view_permissions_cant_be_allowed_lower_down() {
    $root = Item::root();
    $album1 = Test::random_album($root);
    $album2 = Test::random_album($album1);

    $root->reload();
    Access::deny(Identity::everybody(), "view", $root);
    Access::allow(Identity::everybody(), "view", $album2);

    $album1->reload();
    $this->assertFalse(Access::group_can(Identity::everybody(), "view", $album1));

    $album2->reload();
    $this->assertFalse(Access::group_can(Identity::everybody(), "view", $album2));
  }

  public function test_can_edit_item() {
    $root = Item::root();
    Access::allow(Identity::everybody(), "edit", $root);
    $this->assertTrue(Access::group_can(Identity::everybody(), "edit", $root));
  }

  public function test_non_view_permissions_propagate_down() {
    $album = Test::random_album();

    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::reset(Identity::everybody(), "edit", $album);
    $this->assertTrue(Access::group_can(Identity::everybody(), "edit", $album));
  }

  public function test_non_view_permissions_can_be_revoked_lower_down() {
    $outer = Test::random_album();
    $outer_photo = Test::random_photo($outer);

    $inner = Test::random_album($outer);
    $inner_photo = Test::random_photo($inner);

    $outer->reload();
    $inner->reload();

    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::deny(Identity::everybody(), "edit", $outer);
    Access::allow(Identity::everybody(), "edit", $inner);

    // Outer album is not editable, inner one is.
    $this->assertFalse(Access::group_can(Identity::everybody(), "edit", $outer_photo));
    $this->assertTrue(Access::group_can(Identity::everybody(), "edit", $inner_photo));
  }

  public function test_i_can_edit() {
    // Create a new user that belongs to no groups
    $user = Identity::create_user("access_test", "Access Test", "*****", "user@user.com");
    foreach ($user->groups() as $group) {
      $user->remove("groups", $group);
    }
    $user->save();
    Identity::set_active_user($user);

    // This user can't edit anything
    $root = Item::root();
    $this->assertFalse(Access::can("edit", $root));

    // Now add them to a group that has edit permission
    $group = Identity::create_group("access_test");
    $group->add("users", $user);
    $group->save();
    Access::allow($group, "edit", $root);

    $user = Identity::lookup_user($user->id);  // reload() does not flush related columns
    Identity::set_active_user($user);

    // And verify that the user can edit.
    $this->assertTrue(Access::can("edit", $root));
  }

  public function test_everybody_view_permission_maintains_htaccess_files() {
    $album = Test::random_album();

    $this->assertFalse(file_exists($album->file_path() . "/.htaccess"));

    Access::deny(Identity::everybody(), "view", $album);
    $this->assertTrue(file_exists($album->file_path() . "/.htaccess"));

    Access::allow(Identity::everybody(), "view", $album);
    $this->assertFalse(file_exists($album->file_path() . "/.htaccess"));

    Access::deny(Identity::everybody(), "view", $album);
    $this->assertTrue(file_exists($album->file_path() . "/.htaccess"));

    Access::reset(Identity::everybody(), "view", $album);
    $this->assertFalse(file_exists($album->file_path() . "/.htaccess"));
  }

  public function test_everybody_view_full_permission_maintains_htaccess_files() {
    $album = Test::random_album();

    $this->assertFalse(file_exists($album->file_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->resize_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->thumb_path() . "/.htaccess"));

    Access::deny(Identity::everybody(), "view_full", $album);
    $this->assertTrue(file_exists($album->file_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->resize_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->thumb_path() . "/.htaccess"));

    Access::allow(Identity::everybody(), "view_full", $album);
    $this->assertFalse(file_exists($album->file_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->resize_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->thumb_path() . "/.htaccess"));

    Access::deny(Identity::everybody(), "view_full", $album);
    $this->assertTrue(file_exists($album->file_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->resize_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->thumb_path() . "/.htaccess"));

    Access::reset(Identity::everybody(), "view_full", $album);
    $this->assertFalse(file_exists($album->file_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->resize_path() . "/.htaccess"));
    $this->assertFalse(file_exists($album->thumb_path() . "/.htaccess"));
  }

  public function test_moved_items_inherit_new_permissions() {
    Identity::set_active_user(Identity::lookup_user_by_name("admin"));

    $public_album = Test::random_album();
    $public_photo = Test::random_photo($public_album);
    Access::allow(Identity::everybody(), "view", $public_album);
    Access::allow(Identity::everybody(), "edit", $public_album);

    Item::root()->reload();  // Account for MPTT changes

    $private_album = Test::random_album();
    Access::deny(Identity::everybody(), "view", $private_album);
    Access::deny(Identity::everybody(), "edit", $private_album);
    $private_photo = Test::random_photo($private_album);

    // Make sure that we now have a public photo and private photo.
    $this->assertTrue(Access::group_can(Identity::everybody(), "view", $public_photo));
    $this->assertFalse(Access::group_can(Identity::everybody(), "view", $private_photo));

    // Swap the photos
    Item::move($public_photo, $private_album);
    $private_album->reload(); // Reload to get new MPTT pointers and cached perms.
    $public_album->reload();
    $private_photo->reload();
    $public_photo->reload();

    Item::move($private_photo, $public_album);
    $private_album->reload(); // Reload to get new MPTT pointers and cached perms.
    $public_album->reload();
    $private_photo->reload();
    $public_photo->reload();

    // Make sure that the public_photo is now private, and the private_photo is now public.
    $this->assertFalse(Access::group_can(Identity::everybody(), "view", $public_photo));
    $this->assertFalse(Access::group_can(Identity::everybody(), "edit", $public_photo));
    $this->assertTrue(Access::group_can(Identity::everybody(), "view", $private_photo));
    $this->assertTrue(Access::group_can(Identity::everybody(), "edit", $private_photo));
  }
}
