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
class ORM_MPTT_Test extends Gallery_Unit_Test_Case {

  public function add_to_parent_test() {
    $album = test::random_album();

    $this->assert_equal($album->parent()->right_ptr - 2, $album->left_ptr);
    $this->assert_equal($album->parent()->right_ptr - 1, $album->right_ptr);
    $this->assert_equal($album->parent()->level + 1, $album->level);
    $this->assert_equal($album->parent()->id, $album->parent_id);
  }

  public function add_hierarchy_test() {
    $album1 = test::random_album();
    $album1_1 = test::random_album($album1);
    $album1_2 = test::random_album($album1);
    $album1_1_1 = test::random_album($album1_1);
    $album1_1_2 = test::random_album($album1_1);

    $album1->reload();
    $this->assert_equal(9, $album1->right_ptr - $album1->left_ptr);

    $album1_1->reload();
    $this->assert_equal(5, $album1_1->right_ptr - $album1_1->left_ptr);
  }

  public function delete_hierarchy_test() {
    $album1 = test::random_album();
    $album1_1 = test::random_album($album1);
    $album1_2 = test::random_album($album1);
    $album1_1_1 = test::random_album($album1_1);
    $album1_1_2 = test::random_album($album1_1);

    $album1_1->delete();
    $album1->reload();

    // Now album1 contains only album1_2
    $this->assert_equal(3, $album1->right_ptr - $album1->left_ptr);
  }

  public function move_to_test() {
    $album1 = test::random_album();
    $album1_1 = test::random_album($album1);
    $album1_2 = test::random_album($album1);
    $album1_1_1 = test::random_album($album1_1);
    $album1_1_2 = test::random_album($album1_1);

    $album1_2->reload();
    $album1_1_1->reload();

    $album1_1_1->parent_id = $album1_2->id;
    $album1_1_1->save();

    $album1_1->reload();
    $album1_2->reload();

    $this->assert_equal(3, $album1_1->right_ptr - $album1_1->left_ptr);
    $this->assert_equal(3, $album1_2->right_ptr - $album1_2->left_ptr);

    $this->assert_equal(
      array($album1_1_2->id => $album1_1_2->name),
      $album1_1->children()->select_list());

    $this->assert_equal(
      array($album1_1_1->id => $album1_1_1->name),
      $album1_2->children()->select_list());
  }

  public function cant_move_parent_into_own_subtree_test() {
    $album1 = test::random_album(item::root());
    $album2 = test::random_album($album1);
    $album3 = test::random_album($album2);

    try {
      $album1->parent_id = $album3->id;
      $album1->save();
      $this->assert_true(false, "We should be unable to move an item inside its own hierarchy");
    } catch (Exception $e) {
      // pass
    }
  }

  public function parent_test() {
    $album = test::random_album();

    $parent = ORM::factory("item", 1);
    $this->assert_equal($parent->id, $album->parent()->id);
  }

  public function parents_test() {
    $outer = test::random_album();
    $inner = test::random_album($outer);

    $parent_ids = array();
    foreach ($inner->parents() as $parent) {
      $parent_ids[] = $parent->id;
    }
    $this->assert_equal(array(1, $outer->id), $parent_ids);
  }

  public function children_test() {
    $outer = test::random_album();
    $inner1 = test::random_album($outer);
    $inner2 = test::random_album($outer);

    $child_ids = array();
    foreach ($outer->children() as $child) {
      $child_ids[] = $child->id;
    }
    $this->assert_equal(array($inner1->id, $inner2->id), $child_ids);
  }

  public function children_limit_test() {
    $outer = test::random_album();
    $inner1 = test::random_album($outer);
    $inner2 = test::random_album($outer);

    $this->assert_equal(array($inner2->id => $inner2->name),
                        $outer->children(1, 1)->select_list('id'));
  }

  public function children_count_test() {
    $outer = test::random_album();
    $inner1 = test::random_album($outer);
    $inner2 = test::random_album($outer);

    $this->assert_equal(2, $outer->children_count());
  }

  public function descendant_test() {
    $parent = test::random_album();
    $photo = test::random_photo($parent);
    $album1 = test::random_album($parent);
    $photo1 = test::random_photo($album1);

    $parent->reload();

    $this->assert_equal(3, $parent->descendants()->count());
    $this->assert_equal(2, $parent->descendants(null, null, array(array("type", "=", "photo")))->count());
    $this->assert_equal(1, $parent->descendants(null, null, array(array("type", "=", "album")))->count());
  }

  public function descendant_limit_test() {
    $parent = test::random_album();
    $album1 = test::random_album($parent);
    $album2 = test::random_album($parent);
    $album3 = test::random_album($parent);
    $parent->reload();

    $this->assert_equal(2, $parent->descendants(2)->count());
  }

  public function descendant_count_test() {
    $parent = test::random_album();
    $photo = test::random_photo($parent);
    $album1 = test::random_album($parent);
    $photo1 = test::random_photo($album1);
    $parent->reload();

    $this->assert_equal(3, $parent->descendants_count());
    $this->assert_equal(2, $parent->descendants_count(array(array("type", "=", "photo"))));
    $this->assert_equal(1, $parent->descendants_count(array(array("type", "=", "album"))));
  }
}
