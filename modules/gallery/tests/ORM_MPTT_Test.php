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
class ORM_MPTT_Test extends Unittest_Testcase {

  public function test_add_to_parent() {
    $album = Test::random_album();

    $this->assertEquals($album->parent->right_ptr - 2, $album->left_ptr);
    $this->assertEquals($album->parent->right_ptr - 1, $album->right_ptr);
    $this->assertEquals($album->parent->level + 1, $album->level);
    $this->assertEquals($album->parent->id, $album->parent_id);
  }

  public function test_add_hierarchy() {
    $album1 = Test::random_album();
    $album1_1 = Test::random_album($album1);
    $album1_2 = Test::random_album($album1);
    $album1_1_1 = Test::random_album($album1_1);
    $album1_1_2 = Test::random_album($album1_1);

    $album1->reload();
    $this->assertEquals(9, $album1->right_ptr - $album1->left_ptr);

    $album1_1->reload();
    $this->assertEquals(5, $album1_1->right_ptr - $album1_1->left_ptr);
  }

  public function test_delete_hierarchy() {
    $album1 = Test::random_album();
    $album1_1 = Test::random_album($album1);
    $album1_2 = Test::random_album($album1);
    $album1_1_1 = Test::random_album($album1_1);
    $album1_1_2 = Test::random_album($album1_1);

    $album1_1->delete();
    $album1->reload();

    // Now album1 contains only album1_2
    $this->assertEquals(3, $album1->right_ptr - $album1->left_ptr);
  }

  public function test_move_to() {
    $album1 = Test::random_album();
    $album1_1 = Test::random_album($album1);
    $album1_2 = Test::random_album($album1);
    $album1_1_1 = Test::random_album($album1_1);
    $album1_1_2 = Test::random_album($album1_1);

    $album1_2->reload();
    $album1_1_1->reload();

    $album1_1_1->parent_id = $album1_2->id;
    $album1_1_1->save();

    $album1_1->reload();
    $album1_2->reload();

    $this->assertEquals(3, $album1_1->right_ptr - $album1_1->left_ptr);
    $this->assertEquals(3, $album1_2->right_ptr - $album1_2->left_ptr);

    $this->assertEquals($album1_1_2->id, $album1_1->children->find_all()->current()->id);
    $this->assertEquals($album1_1_1->id, $album1_2->children->find_all()->current()->id);
  }

  public function test_cant_move_parent_into_own_subtree() {
    $album1 = Test::random_album(Item::root());
    $album2 = Test::random_album($album1);
    $album3 = Test::random_album($album2);

    try {
      $album1->parent_id = $album3->id;
      $album1->save();
      $this->assertTrue(false, "We should be unable to move an item inside its own hierarchy");
    } catch (Exception $e) {
      // pass
    }
  }

  public function test_parent() {
    $album = Test::random_album();

    $parent = ORM::factory("Item", 1);
    $this->assertEquals($parent->id, $album->parent->id);
  }

  public function test_parents() {
    $outer = Test::random_album();
    $inner = Test::random_album($outer);

    $parent_ids = array();
    foreach ($inner->parents->find_all() as $parent) {
      $parent_ids[] = $parent->id;
    }
    $this->assertEquals(array(1, $outer->id), $parent_ids);
  }

  public function test_children() {
    $outer = Test::random_album();
    $inner1 = Test::random_album($outer);
    $inner2 = Test::random_album($outer);

    $child_ids = array();
    foreach ($outer->children->find_all() as $child) {
      $child_ids[] = $child->id;
    }
    $this->assertEquals(array($inner1->id, $inner2->id), $child_ids);
  }

  public function test_children_limit() {
    $outer = Test::random_album();
    $inner1 = Test::random_album($outer);
    $inner2 = Test::random_album($outer);

    $this->assertEquals(
      $inner2->id, $outer->children->offset(1)->find()->id);
  }

  public function test_children_count() {
    $outer = Test::random_album();
    $inner1 = Test::random_album($outer);
    $inner2 = Test::random_album($outer);

    $this->assertEquals(2, $outer->children->count_all());
  }

  public function test_descendant() {
    $parent = Test::random_album();
    $photo = Test::random_photo($parent);
    $album1 = Test::random_album($parent);
    $photo1 = Test::random_photo($album1);

    $parent->reload();

    $this->assertEquals(3, $parent->descendants->count_all());
    $this->assertEquals(2, $parent->descendants->where("type", "=", "photo")->count_all());
    $this->assertEquals(1, $parent->descendants->where("type", "=", "album")->count_all());
  }

  public function test_descendant_count() {
    $parent = Test::random_album();
    $photo = Test::random_photo($parent);
    $album1 = Test::random_album($parent);
    $photo1 = Test::random_photo($album1);
    $parent->reload();

    $this->assertEquals(3, $parent->descendants->count_all());
    $this->assertEquals(2, $parent->descendants->where("type", "=", "photo")->count_all());
    $this->assertEquals(1, $parent->descendants->where("type", "=", "album")->count_all());
  }

  public function test_contains() {
    $parent = Test::random_album();
    $photo = Test::random_photo($parent);
    $album1 = Test::random_album($parent);
    $photo1 = Test::random_photo($album1);
    $parent->reload();

    $this->assertEquals(true, $parent->contains($photo1));        // grandchild
    $this->assertEquals(true, $parent->contains($photo));         // child
    $this->assertEquals(true, $photo->contains($photo));          // self
    $this->assertEquals(false, $photo->contains($photo, false));  // self (with include_self)
    $this->assertEquals(false, $album1->contains($photo));        // sibling
    $this->assertEquals(false, $album1->contains($parent));       // parent
    $this->assertEquals(false, $photo1->contains($parent));       // grandparent
  }

  public function test_fail_on_viewable_before_special_relationships() {
    Identity::set_active_user(Identity::guest());
    try {
      Item::root()->viewable()->children;
      $this->assertTrue(false);
    } catch (Kohana_Exception $e) {
    }

    try {
      Item::root()->viewable()->parents;
      $this->assertTrue(false);
    } catch (Kohana_Exception $e) {
    }

    try {
      Item::root()->viewable()->descendants;
      $this->assertTrue(false);
    } catch (Kohana_Exception $e) {
    }
  }
}
