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
    $item = test::random_photo();
    $this->assert_true(!empty($item->created));
    $this->assert_true(!empty($item->updated));
  }

  public function updating_doesnt_change_created_date_test() {
    $item = test::random_photo();

    // Force the creation date to something well known
    db::build()
      ->update("items")
      ->set("created", 0)
      ->set("updated", 0)
      ->where("id", "=", $item->id)
      ->execute();
    $item->reload();
    $item->title = "foo";  // force a change
    $item->save();

    $this->assert_true(empty($item->created));
    $this->assert_true(!empty($item->updated));
  }

  public function updating_view_count_only_doesnt_change_updated_date_test() {
    $item = test::random_photo();
    $item->reload();
    $this->assert_equal(0, $item->view_count);

    // Force the updated date to something well known
    db::build()
      ->update("items")
      ->set("updated", 0)
      ->where("id", "=", $item->id)
      ->execute();
    $item->reload();
    $item->view_count++;
    $item->save();

    $this->assert_same(1, $item->view_count);
    $this->assert_true(empty($item->updated));
  }

  public function rename_photo_test() {
    $item = test::random_photo();

    file_put_contents($item->thumb_path(), "thumb");
    file_put_contents($item->resize_path(), "resize");
    file_put_contents($item->file_path(), "file");

    $original_name = $item->name;
    $new_name = rand();

    // Now rename it
    $item->name = $new_name;
    $item->save();

    // Expected: the name changed, the name is now baked into all paths, and all files were moved.
    $this->assert_equal($new_name, $item->name);
    $this->assert_equal($new_name, basename($item->file_path()));
    $this->assert_equal($new_name, basename($item->thumb_path()));
    $this->assert_equal($new_name, basename($item->resize_path()));
    $this->assert_equal("thumb", file_get_contents($item->thumb_path()));
    $this->assert_equal("resize", file_get_contents($item->resize_path()));
    $this->assert_equal("file", file_get_contents($item->file_path()));
  }

  public function rename_album_test() {
    $album = test::random_album();
    $photo = test::random_photo($album);
    $album->reload();

    file_put_contents($photo->thumb_path(), "thumb");
    file_put_contents($photo->resize_path(), "resize");
    file_put_contents($photo->file_path(), "file");

    $original_album_name = $album->name;
    $original_photo_name = $photo->name;
    $new_album_name = rand();

    // Now rename the album
    $album->name = $new_album_name;
    $album->save();
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
    $item = test::random_photo();

    $new_name = rand() . "/";

    try {
      $item->rename($new_name)->save();
    } catch (Exception $e) {
      // pass
      return;
    }
    $this->assert_false(true, "Item_Model::rename should not accept / characters");
  }

  public function item_rename_fails_with_existing_name_test() {
    // Create a test photo
    $item = test::random_photo();
    $item2 = test::random_photo();

    try {
      $item->name = $item2->name;
      $item->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_true(in_array("conflict", $e->validation->errors()));
      return;
    }

    $this->assert_false(true, "rename should conflict");
  }

  public function save_original_values_test() {
    $item = test::random_photo_unsaved();
    $item->title = "ORIGINAL_VALUE";
    $item->save();
    $item->title = "NEW_VALUE";

    $this->assert_same("ORIGINAL_VALUE", $item->original()->title);
    $this->assert_same("NEW_VALUE", $item->title);
  }

  public function move_album_test() {
    $album2 = test::random_album();
    $album = test::random_album($album2);
    $photo = test::random_photo($album);

    file_put_contents($photo->thumb_path(), "thumb");
    file_put_contents($photo->resize_path(), "resize");
    file_put_contents($photo->file_path(), "file");

    // Now move the album
    $album->move_to(item::root());
    $photo->reload();

    // Expected:
    // * the album dirs are all moved
    // * the photo's paths are all inside the albums paths
    // * the photo files are all still intact and accessible

    $this->assert_same(0, strpos($photo->file_path(), $album->file_path()));
    $this->assert_same(0, strpos($photo->thumb_path(), dirname($album->thumb_path())));
    $this->assert_same(0, strpos($photo->resize_path(), dirname($album->resize_path())));

    $this->assert_equal("thumb", file_get_contents($photo->thumb_path()));
    $this->assert_equal("resize", file_get_contents($photo->resize_path()));
    $this->assert_equal("file", file_get_contents($photo->file_path()));
  }

  public function move_photo_test() {
    $album2 = test::random_album();
    $album = test::random_album($album2);
    $photo  = test::random_photo($album);

    file_put_contents($photo->thumb_path(), "thumb");
    file_put_contents($photo->resize_path(), "resize");
    file_put_contents($photo->file_path(), "file");

    // Now move the album
    $photo->move_to($album2);
    $photo->reload();

    // Expected:
    // * the album dirs are all moved
    // * the photo's paths are all inside the albums paths
    // * the photo files are all still intact and accessible

    $this->assert_same(0, strpos($photo->file_path(), $album->file_path()));
    $this->assert_same(0, strpos($photo->thumb_path(), dirname($album->thumb_path())));
    $this->assert_same(0, strpos($photo->resize_path(), dirname($album->resize_path())));

    $this->assert_equal("thumb", file_get_contents($photo->thumb_path()));
    $this->assert_equal("resize", file_get_contents($photo->resize_path()));
    $this->assert_equal("file", file_get_contents($photo->file_path()));
  }

  public function move_album_fails_invalid_target_test() {
    $album = test::random_album();
    $source = test::random_album($album);

    try {
      $source->move_to(item::root());
    } catch (Exception $e) {
      // pass
      $this->assert_true(strpos($e->getMessage(), "INVALID_MOVE_TARGET_EXISTS") !== false,
                         "incorrect exception.");
      return;
    }
  }

  public function move_photo_fails_invalid_target_test() {
    $photo1 = test::random_photo();
    $album = test::random_album();
    $photo2 = test::random_photo($album);

    try {
      $photo2->move_to(item::root());
    } catch (Exception $e) {
      // pass
      $this->assert_true(strpos($e->getMessage(), "INVALID_MOVE_TARGET_EXISTS") !== false,
                         "incorrect exception.");
      return;
    }
  }

  public function basic_validation_test() {
    $item = ORM::factory("item");
    $item->album_cover_item_id = rand();  // invalid
    $item->description = str_repeat("x", 70000);  // invalid
    $item->name = null;
    $item->parent_id = rand();
    $item->slug = null;
    $item->sort_column = "bogus";
    $item->sort_order = "bogus";
    $item->title = null;
    $item->type = "bogus";
    try {
      $item->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_same(array("description" => "length",
                               "name" => "required",
                               "slug" => "required",
                               "title" => "required",
                               "album_cover_item_id" => "invalid_item",
                               "parent_id" => "invalid",
                               "sort_column" => "invalid",
                               "sort_order" => "invalid",
                               "type" => "invalid"),
                         $e->validation->errors());
      return;
    }

    $this->assert_false(true, "Shouldn't get here");
  }
}
