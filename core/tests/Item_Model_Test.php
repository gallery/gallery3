<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
    return $item->add_to_parent(ORM::factory("item", 1));
  }

  public function updating_doesnt_change_created_date_test() {
    $item = self::create_random_item();

    // Force the creation date to something well known
    $db = Database::instance();
    $db->update("items", array("created" => 0, "updated" => 0), array("id" => $item->id));
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
    $db->update("items", array("updated" => 0), array("id" => $item->id));
    $item->reload();
    $item->view_count++;
    $item->save();

    $this->assert_same(1, $item->view_count);
    $this->assert_true(empty($item->updated));
  }

  public function move_photo_test() {
    // Create a test photo
    $item = self::create_random_item();

    file_put_contents($item->thumb_path(), "thumb");
    file_put_contents($item->resize_path(), "resize");
    file_put_contents($item->file_path(), "file");

    $original_name = $item->name;
    $new_name = rand();

    // Now rename it
    $item->rename($new_name)->save();

    // Expected: the name changed, the name is now baked into all paths, and all files were moved.
    $this->assert_equal($new_name, $item->name);
    $this->assert_equal($new_name, basename($item->file_path()));
    $this->assert_equal($new_name, basename($item->thumb_path()));
    $this->assert_equal($new_name, basename($item->resize_path()));
    $this->assert_equal("thumb", file_get_contents($item->thumb_path()));
    $this->assert_equal("resize", file_get_contents($item->resize_path()));
    $this->assert_equal("file", file_get_contents($item->file_path()));
  }

  public function move_album_test() {
    // Create an album with a photo in it
    $root = ORM::factory("item", 1);
    $album = album::create($root, rand(), rand(), rand());
    $photo = ORM::factory("item");
    $photo->name = rand();
    $photo->type = "photo";
    $photo->add_to_parent($album);

    file_put_contents($photo->thumb_path(), "thumb");
    file_put_contents($photo->resize_path(), "resize");
    file_put_contents($photo->file_path(), "file");

    $original_album_name = $album->name;
    $original_photo_name = $photo->name;
    $new_album_name = rand();

    // Now rename the album
    $album->rename($new_album_name)->save();
    $photo->reload();

    // Expected:
    // * the album name changed.
    // * the album dirs are all moved
    // * the photo's paths are all inside the albums paths
    // * the photo files are all still intact and accessible
    $this->assert_equal($new_album_name, $album->name);
    $this->assert_equal($new_album_name, basename($album->file_path()));
    $this->assert_equal($new_album_name, basename(dirname($album->thumb_path())));
    $this->assert_equal($new_album_name, basename(dirname($album->resize_path())));

    $this->assert_same(0, strpos($photo->file_path(), $album->file_path()));
    $this->assert_same(0, strpos($photo->thumb_path(), dirname($album->thumb_path())));
    $this->assert_same(0, strpos($photo->resize_path(), dirname($album->resize_path())));

    $this->assert_equal("thumb", file_get_contents($photo->thumb_path()));
    $this->assert_equal("resize", file_get_contents($photo->resize_path()));
    $this->assert_equal("file", file_get_contents($photo->file_path()));
  }

  public function item_rename_wont_accept_slash_test() {
    // Create a test photo
    $item = self::create_random_item();

    $new_name = rand() . "/";

    try {
      $item->rename($new_name)->save();
    } catch (Exception $e) {
      // pass
      return;
    }
    $this->assert_false(true, "Item_Model::rename should not accept / characters");
  }
}
