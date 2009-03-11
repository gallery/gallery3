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

  private function create_item_and_add_to_parent($parent) {
    $album = ORM::factory("item");
    $album->type = "album";
    $album->rand_key = ((float)mt_rand()) / (float)mt_getrandmax();
    $album->sort_column = "id";
    $album->sort_order = "ASC";
    $album->add_to_parent($parent);
    return $album;
  }

  public function add_to_parent_test() {
    $root = ORM::factory("item", 1);
    $album = ORM::factory("item");
    $album->type = "album";
    $album->rand_key = ((float)mt_rand()) / (float)mt_getrandmax();
    $album->sort_column = "id";
    $album->sort_order = "ASC";
    $album->add_to_parent($root);

    $this->assert_equal($album->parent()->right - 2, $album->left);
    $this->assert_equal($album->parent()->right - 1, $album->right);
    $this->assert_equal($album->parent()->level + 1, $album->level);
    $this->assert_equal($album->parent()->id, $album->parent_id);
  }

  public function add_hierarchy_test() {
    $root = ORM::factory("item", 1);
    $album1 = self::create_item_and_add_to_parent($root);
    $album1_1 = self::create_item_and_add_to_parent($album1);
    $album1_2 = self::create_item_and_add_to_parent($album1);
    $album1_1_1 = self::create_item_and_add_to_parent($album1_1);
    $album1_1_2 = self::create_item_and_add_to_parent($album1_1);

    $album1->reload();
    $this->assert_equal(9, $album1->right - $album1->left);

    $album1_1->reload();
    $this->assert_equal(5, $album1_1->right - $album1_1->left);
  }

  public function delete_hierarchy_test() {
    $root = ORM::factory("item", 1);
    $album1 = self::create_item_and_add_to_parent($root);
    $album1_1 = self::create_item_and_add_to_parent($album1);
    $album1_2 = self::create_item_and_add_to_parent($album1);
    $album1_1_1 = self::create_item_and_add_to_parent($album1_1);
    $album1_1_2 = self::create_item_and_add_to_parent($album1_1);

    $album1_1->delete();
    $album1->reload();

    // Now album1 contains only album1_2
    $this->assert_equal(3, $album1->right - $album1->left);
  }

  public function move_to_test() {
    $root = ORM::factory("item", 1);
    $album1 = album::create($root, "move_to_test_1", "move_to_test_1");
    $album1_1 = album::create($album1, "move_to_test_1_1", "move_to_test_1_1");
    $album1_2 = album::create($album1, "move_to_test_1_2", "move_to_test_1_2");
    $album1_1_1 = album::create($album1_1, "move_to_test_1_1_1", "move_to_test_1_1_1");
    $album1_1_2 = album::create($album1_1, "move_to_test_1_1_2", "move_to_test_1_1_2");

    $album1_2->reload();
    $album1_1_1->reload();

    $album1_1_1->move_to($album1_2);

    $album1_1->reload();
    $album1_2->reload();

    $this->assert_equal(3, $album1_1->right - $album1_1->left);
    $this->assert_equal(3, $album1_2->right - $album1_2->left);

    $this->assert_equal(
      array($album1_1_2->id => "move_to_test_1_1_2"),
      $album1_1->children()->select_list());

    $this->assert_equal(
      array($album1_1_1->id => "move_to_test_1_1_1"),
      $album1_2->children()->select_list());
  }

  public function parent_test() {
    $root = ORM::factory("item", 1);
    $album = self::create_item_and_add_to_parent($root);

    $parent = ORM::factory("item", 1);
    $this->assert_equal($parent->id, $album->parent()->id);
  }

  public function parents_test() {
    $root = ORM::factory("item", 1);
    $outer = self::create_item_and_add_to_parent($root);
    $inner = self::create_item_and_add_to_parent($outer);

    $parent_ids = array();
    foreach ($inner->parents() as $parent) {
      $parent_ids[] = $parent->id;
    }
    $this->assert_equal(array(1, $outer->id), $parent_ids);
  }

  public function children_test() {
    $root = ORM::factory("item", 1);
    $outer = self::create_item_and_add_to_parent($root);
    $inner1 = self::create_item_and_add_to_parent($outer);
    $inner2 = self::create_item_and_add_to_parent($outer);

    $child_ids = array();
    foreach ($outer->children() as $child) {
      $child_ids[] = $child->id;
    }
    $this->assert_equal(array($inner1->id, $inner2->id), $child_ids);
  }

  public function children_limit_test() {
    $root = ORM::factory("item", 1);
    $outer = self::create_item_and_add_to_parent($root);
    $inner1 = self::create_item_and_add_to_parent($outer);
    $inner2 = self::create_item_and_add_to_parent($outer);

    $this->assert_equal(array($inner2->id => null), $outer->children(1, 1)->select_list('id'));
  }

  public function children_count_test() {
    $root = ORM::factory("item", 1);
    $outer = self::create_item_and_add_to_parent($root);
    $inner1 = self::create_item_and_add_to_parent($outer);
    $inner2 = self::create_item_and_add_to_parent($outer);

    $this->assert_equal(2, $outer->children_count());
  }

  public function descendant_test() {
    $root = ORM::factory("item", 1);

    $parent = ORM::factory("item");
    $parent->type = "album";
    $parent->rand_key = ((float)mt_rand()) / (float)mt_getrandmax();
    $parent->sort_column = "id";
    $parent->sort_order = "ASC";
    $parent->add_to_parent($root);

    $photo = ORM::factory("item");
    $photo->type = "photo";
    $photo->add_to_parent($parent);

    $album1 = ORM::factory("item");
    $album1->type = "album";
    $album1->rand_key = ((float)mt_rand()) / (float)mt_getrandmax();
    $album1->sort_column = "id";
    $album1->sort_order = "ASC";
    $album1->add_to_parent($parent);

    $photo1 = ORM::factory("item");
    $photo1->type = "photo";
    $photo1->add_to_parent($album1);

    $parent->reload();

    $this->assert_equal(3, $parent->descendants()->count());
    $this->assert_equal(2, $parent->descendants(null, 0, "photo")->count());
    $this->assert_equal(1, $parent->descendants(null, 0, "album")->count());
  }

  public function descendant_limit_test() {
    $root = ORM::factory("item", 1);

    $parent = self::create_item_and_add_to_parent($root);
    $album1 = self::create_item_and_add_to_parent($parent);
    $album2 = self::create_item_and_add_to_parent($parent);
    $album3 = self::create_item_and_add_to_parent($parent);

    $parent->reload();
    $this->assert_equal(2, $parent->descendants(2)->count());
  }

  public function descendant_count_test() {
    $root = ORM::factory("item", 1);

    $parent = ORM::factory("item");
    $parent->type = "album";
    $parent->add_to_parent($root);

    $photo = ORM::factory("item");
    $photo->type = "photo";
    $photo->add_to_parent($parent);

    $album1 = ORM::factory("item");
    $album1->type = "album";
    $album1->add_to_parent($parent);

    $photo1 = ORM::factory("item");
    $photo1->type = "photo";
    $photo1->add_to_parent($album1);

    $parent->reload();

    $this->assert_equal(3, $parent->descendants_count());
    $this->assert_equal(2, $parent->descendants_count("photo"));
    $this->assert_equal(1, $parent->descendants_count("album"));
  }
}
