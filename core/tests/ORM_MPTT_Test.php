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
class ORM_MPTT_Test extends Unit_Test_Case {
  public function add_to_parent_test() {
    $album = ORM::factory("item")->add_to_parent(1);

    $this->assert_equal($album->parent()->right - 2, $album->left);
    $this->assert_equal($album->parent()->right - 1, $album->right);
    $this->assert_equal($album->parent()->level + 1, $album->level);
    $this->assert_equal($album->parent()->id, $album->parent_id);
  }

  public function add_hierarchy_test() {
    $album1 = ORM::factory("item")->add_to_parent(1);
    $album1_1 = ORM::factory("item")->add_to_parent($album1->id);
    $album1_2 = ORM::factory("item")->add_to_parent($album1->id);
    $album1_1_1 = ORM::factory("item")->add_to_parent($album1_1->id);
    $album1_1_2 = ORM::factory("item")->add_to_parent($album1_1->id);

    $album1->reload();
    $this->assert_equal(9, $album1->right - $album1->left);

    $album1_1->reload();
    $this->assert_equal(5, $album1_1->right - $album1_1->left);
  }

  public function delete_hierarchy_test() {
    $album1 = ORM::factory("item")->add_to_parent(1);
    $album1_1 = ORM::factory("item")->add_to_parent($album1->id);
    $album1_2 = ORM::factory("item")->add_to_parent($album1->id);
    $album1_1_1 = ORM::factory("item")->add_to_parent($album1_1->id);
    $album1_1_2 = ORM::factory("item")->add_to_parent($album1_1->id);

    $album1_1->delete();
    $album1->reload();

    // Now album1 contains only album1_2
    $this->assert_equal(3, $album1->right - $album1->left);
  }

  public function move_to_test() {
    $album1 = album::create(1, "move_to_test_1", "move_to_test_1");
    $album1_1 = album::create($album1->id, "move_to_test_1_1", "move_to_test_1_1");
    $album1_2 = album::create($album1->id, "move_to_test_1_2", "move_to_test_1_2");
    $album1_1_1 = album::create($album1_1->id, "move_to_test_1_1_1", "move_to_test_1_1_1");
    $album1_1_2 = album::create($album1_1->id, "move_to_test_1_1_2", "move_to_test_1_1_2");

    $album1_2->reload();
    $album1_1_1->reload();

    $album1_1_1->moveTo($album1_2);

    $album1_1->reload();
    $album1_2->reload();

    $this->assert_equal(3, $album1_1->right - $album1_1->left);
    $this->assert_equal(3, $album1_2->right - $album1_2 ->left);
  }

  public function parent_test() {
    $album = ORM::factory("item")->add_to_parent(1);

    $parent = ORM::factory("item", 1);
    $this->assert_equal($parent->id, $album->parent()->id);
  }

  public function parents_test() {
    $outer = ORM::factory("item")->add_to_parent(1);
    $inner = ORM::factory("item")->add_to_parent($outer->id);

    $parent_ids = array();
    foreach ($inner->parents() as $parent) {
      $parent_ids[] = $parent->id;
    }
    $this->assert_equal(array(1, $outer->id), $parent_ids);
  }

  public function children_test() {
    $outer = ORM::factory("item")->add_to_parent(1);
    $inner1 = ORM::factory("item")->add_to_parent($outer->id);
    $inner2 = ORM::factory("item")->add_to_parent($outer->id);

    $child_ids = array();
    foreach ($outer->children() as $child) {
      $child_ids[] = $child->id;
    }
    $this->assert_equal(array($inner1->id, $inner2->id), $child_ids);
  }

  public function children_limit_test() {
    $outer = ORM::factory("item")->add_to_parent(1);
    $inner1 = ORM::factory("item")->add_to_parent($outer->id);
    $inner2 = ORM::factory("item")->add_to_parent($outer->id);

    $this->assert_equal(array($inner2->id => null), $outer->children(1, 1)->select_list('id'));
  }

  public function children_count_test() {
    $outer = ORM::factory("item")->add_to_parent(1);
    $inner1 = ORM::factory("item")->add_to_parent($outer->id);
    $inner2 = ORM::factory("item")->add_to_parent($outer->id);

    $this->assert_equal(2, $outer->children_count());
  }

  public function descendant_test() {
    $parent = ORM::factory("item");
    $parent->type = "album";
    $parent->add_to_parent(1);

    $photo = ORM::factory("item");
    $photo->type = "photo";
    $photo->add_to_parent($parent->id);

    $album1 = ORM::factory("item");
    $album1->type = "album";
    $album1->add_to_parent($parent->id);

    $photo1 = ORM::factory("item");
    $photo1->type = "photo";
    $photo1->add_to_parent($album1->id);

    $parent->reload();

    $this->assert_equal(3, $parent->descendants()->count());
    $this->assert_equal(2, $parent->descendants(null, 0, "photo")->count());
    $this->assert_equal(1, $parent->descendants(null, 0, "album")->count());
  }

  public function descendant_limit_test() {
    $parent = ORM::factory("item")->add_to_parent(1);
    $album1 = ORM::factory("item")->add_to_parent($parent->id);
    $album2 = ORM::factory("item")->add_to_parent($parent->id);
    $album3 = ORM::factory("item")->add_to_parent($parent->id);

    $parent->reload();
    $this->assert_equal(2, $parent->descendants(2)->count());
  }

  public function descendant_count_test() {
    $parent = ORM::factory("item");
    $parent->type = "album";
    $parent->add_to_parent(1);

    $photo = ORM::factory("item");
    $photo->type = "photo";
    $photo->add_to_parent($parent->id);

    $album1 = ORM::factory("item");
    $album1->type = "album";
    $album1->add_to_parent($parent->id);

    $photo1 = ORM::factory("item");
    $photo1->type = "photo";
    $photo1->add_to_parent($album1->id);

    $parent->reload();

    $this->assert_equal(3, $parent->descendants_count());
    $this->assert_equal(2, $parent->descendants_count("photo"));
    $this->assert_equal(1, $parent->descendants_count("album"));
  }
}
