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
class Item_Model_Test extends Unit_Test_Case {
  public function saving_sets_created_and_updated_dates_test() {
    $item = self::create_random_item();
    $this->assert_true(!empty($item->created));
    $this->assert_true(!empty($item->updated));
  }

  private function create_random_item() {
    $item = ORM::factory("item");
    /* Set all required fields (values are irrelevant) */
    $item->name = rand();
    $item->type = "photo";
    $item->left = 1;
    $item->right = 1;
    $item->level = 1;
    $item->parent_id = 1;
    $item->save();
    return $item;
  }

  public function updating_doesnt_change_created_date_test() {
    $item = self::create_random_item();

    // Force the creation date to something well known
    $db = Database::instance();
    $db->query("UPDATE `items` SET `created` = 0 WHERE `id` = $item->id");
    $db->query("UPDATE `items` SET `updated` = 0 WHERE `id` = $item->id");
    $item->reload();
    $item->title = "foo";  // force a change
    $item->save();

    $this->assert_true(empty($item->created));
    $this->assert_true(!empty($item->updated));
  }

  public function updating_view_count_only_doesnt_change_updated_date_test() {
    $item = self::create_random_item();
    $item->reload();
    $this->assert_same(0, $item->view_count);

    // Force the updated date to something well known
    $db = Database::instance();
    $db->query("UPDATE `items` SET `updated` = 0 WHERE `id` = $item->id");
    $item->reload();
    $item->view_count++;
    $item->save();

    $this->assert_same(1, $item->view_count);
    $this->assert_true(empty($item->updated));
  }
}
