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
    $album = ORM::factory("item");
    $album->type = "photo";
    $album->title = "test";
    $album->name = "test";
    $album->add_to_parent(1);

    $this->assert_equal($album->parent()->right - 2, $album->left);
    $this->assert_equal($album->parent()->right - 1, $album->right);
    $this->assert_equal($album->parent()->level + 1, $album->level);
    $this->assert_equal($album->parent()->id, $album->parent_id);
  }

  public function parent_test() {
    $album = ORM::factory("item");
    $album->add_to_parent(1);

    $parent = ORM::factory("item", 1);
    $this->assert_equal($parent->id, $album->parent()->id);
  }

  public function parents_test() {
    $outer = ORM::factory("item");
    $outer->add_to_parent(1);

    $inner = ORM::factory("item");
    $inner->add_to_parent($outer->id);

    $parent_ids = array();
    foreach ($inner->parents() as $parent) {
      $parent_ids[] = $parent->id;
    }
    $this->assert_equal(array(1, $outer->id), $parent_ids);
  }

  public function children_test() {
    $outer = ORM::factory("item");
    $outer->add_to_parent(1);

    $inner1 = ORM::factory("item");
    $inner1->add_to_parent($outer->id);

    $inner2 = ORM::factory("item");
    $inner2->add_to_parent($outer->id);

    $child_ids = array();
    foreach ($outer->children() as $child) {
      $child_ids[] = $child->id;
    }
    $this->assert_equal(array($inner1->id, $inner2->id), $child_ids);
  }

  public function children_limit_test() {
    $outer = ORM::factory("item");
    $outer->add_to_parent(1);

    $inner1 = ORM::factory("item");
    $inner1->add_to_parent($outer->id);

    $inner2 = ORM::factory("item");
    $inner2->add_to_parent($outer->id);

    $this->assert_equal(array($inner2->id => null), $outer->children(1, 1)->select_list('id'));
  }

  public function children_count_test() {
    $outer = ORM::factory("item");
    $outer->add_to_parent(1);

    $inner1 = ORM::factory("item");
    $inner1->add_to_parent($outer->id);

    $inner2 = ORM::factory("item");
    $inner2->add_to_parent($outer->id);
    $this->assert_equal(2, $outer->children_count());
  }
}
