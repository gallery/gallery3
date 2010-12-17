<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Item_Model_Test extends Gallery_Unit_Test_Case {
  public function teardown() {
    identity::set_active_user(identity::admin_user());
  }

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
    $original_name = $item->name;

    file_put_contents($item->thumb_path(), "thumb");
    file_put_contents($item->resize_path(), "resize");
    file_put_contents($item->file_path(), "file");

    // Now rename it
    $item->name = ($new_name = test::random_name($item));
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
    $new_album_name = test::random_name();

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

    $this->assert_true(test::starts_with($photo->file_path(), $album->file_path()));
    $this->assert_true(test::starts_with($photo->thumb_path(), dirname($album->thumb_path())));
    $this->assert_true(test::starts_with($photo->resize_path(), dirname($album->resize_path())));

    $this->assert_equal("thumb", file_get_contents($photo->thumb_path()));
    $this->assert_equal("resize", file_get_contents($photo->resize_path()));
    $this->assert_equal("file", file_get_contents($photo->file_path()));
  }

  public function item_rename_wont_accept_slash_test() {
    $item = test::random_photo();
    try {
      $item->name = test::random_name() . "/";
      $item->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("name" => "no_slashes"), $e->validation->errors());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function item_rename_over_existing_name_gets_uniqified_test() {
    // Create a test photo
    $item = test::random_photo();
    $item2 = test::random_photo();

    $item->name = $item2->name;
    $item->save();

    // foo.jpg should become foo-####.jpg
    $this->assert_true(
      preg_match("/" . str_replace(".jpg", "", $item2->name) . "-\d+\.jpg/", $item->name));
  }

  public function move_album_test() {
    $album2 = test::random_album();
    $album1 = test::random_album($album2);
    $photo = test::random_photo($album1);

    file_put_contents($photo->thumb_path(), "thumb");
    file_put_contents($photo->resize_path(), "resize");
    file_put_contents($photo->file_path(), "file");

    // Now move the album
    $album1->parent_id = item::root()->id;
    $album1->save();
    $photo->reload();

    // Expected:
    // * album is not inside album2 anymore
    // * the photo's paths are all inside the albums paths
    // * the photo files are all still intact and accessible

    $this->assert_false(test::starts_with($album2->file_path(), $album1->file_path()));
    $this->assert_true(test::starts_with($photo->file_path(), $album1->file_path()));
    $this->assert_true(test::starts_with($photo->thumb_path(), dirname($album1->thumb_path())));
    $this->assert_true(test::starts_with($photo->resize_path(), dirname($album1->resize_path())));

    $this->assert_equal("thumb", file_get_contents($photo->thumb_path()));
    $this->assert_equal("resize", file_get_contents($photo->resize_path()));
    $this->assert_equal("file", file_get_contents($photo->file_path()));
  }

  public function move_photo_test() {
    $album1 = test::random_album();
    $photo  = test::random_photo($album1);

    $album2 = test::random_album();

    file_put_contents($photo->thumb_path(), "thumb");
    file_put_contents($photo->resize_path(), "resize");
    file_put_contents($photo->file_path(), "file");

    // Now move the photo
    $photo->parent_id = $album2->id;
    $photo->save();

    // Expected:
    // * the photo's paths are inside the album2 not album1
    // * the photo files are all still intact and accessible

    $this->assert_true(test::starts_with($photo->file_path(), $album2->file_path()));
    $this->assert_true(test::starts_with($photo->thumb_path(), dirname($album2->thumb_path())));
    $this->assert_true(test::starts_with($photo->resize_path(), dirname($album2->resize_path())));

    $this->assert_equal("thumb", file_get_contents($photo->thumb_path()));
    $this->assert_equal("resize", file_get_contents($photo->resize_path()));
    $this->assert_equal("file", file_get_contents($photo->file_path()));
  }

  public function move_album_with_conflicting_target_gets_uniqified_test() {
    $album = test::random_album();
    $source = test::random_album_unsaved($album);
    $source->name = $album->name;
    $source->save();

    // $source and $album have the same name, so if we move $source into the root they should
    // conflict and get randomized

    $source->parent_id = item::root()->id;
    $source->save();

    // foo should become foo-####
    $this->assert_true(preg_match("/{$album->name}-\d+/", $source->name));
    $this->assert_true(preg_match("/{$album->slug}-\d+/", $source->slug));
  }

  public function move_album_fails_wrong_target_type_test() {
    $album = test::random_album();
    $photo = test::random_photo();

    // $source and $album have the same name, so if we move $source into the root they should
    // conflict.

    try {
      $album->parent_id = $photo->id;
      $album->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("parent_id" => "invalid"), $e->validation->errors());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function move_photo_with_conflicting_target_gets_uniqified_test() {
    $photo1 = test::random_photo();
    $album = test::random_album();
    $photo2 = test::random_photo_unsaved($album);
    $photo2->name = $photo1->name;
    $photo2->save();

    // $photo1 and $photo2 have the same name, so if we move $photo1 into the root they should
    // conflict and get uniqified.

    $photo2->parent_id = item::root()->id;
    $photo2->save();

    // foo.jpg should become foo-####.jpg
    $this->assert_true(
      preg_match("/" . str_replace(".jpg", "", $photo1->name) . "-\d+\.jpg/", $photo2->name));

    // foo should become foo
    $this->assert_true(preg_match("/{$photo1->slug}/", $photo2->name));
  }

  public function move_album_inside_descendent_fails_test() {
    $album1 = test::random_album();
    $album2 = test::random_album($album1);
    $album3 = test::random_album($album2);

    try {
      $album1->parent_id = $album3->id;
      $album1->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("parent_id" => "invalid"), $e->validation->errors());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }


  public function basic_validation_test() {
    $item = ORM::factory("item");
    $item->album_cover_item_id = random::int();  // invalid
    $item->description = str_repeat("x", 70000);  // invalid
    $item->name = null;
    $item->parent_id = random::int();
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

  public function slug_is_url_safe_test() {
    try {
      $album = test::random_album_unsaved();
      $album->slug = "illegal chars! !@#@#$!@~";
      $album->save();
      $this->assert_true(false, "Shouldn't be able to save");
    } catch (ORM_Validation_Exception $e) {
      $this->assert_same(array("slug" => "not_url_safe"), $e->validation->errors());
    }

    // This should work
    $album->slug = "the_quick_brown_fox";
    $album->save();
  }

  public function name_with_only_invalid_chars_is_still_valid_test() {
    $album = test::random_album_unsaved();
    $album->name = "[]";
    $album->save();
  }

  public function cant_change_item_type_test() {
    $photo = test::random_photo();
    try {
      $photo->type = "movie";
      $photo->mime_type = "video/x-flv";
      $photo->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_same(array("type" => "read_only"), $e->validation->errors());
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function cant_delete_root_album_test() {
    try {
      item::root()->delete();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_same(array("id" => "cant_delete_root_album"), $e->validation->errors());
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function as_restful_array_test() {
    $album = test::random_album();
    $photo = test::random_photo($album);
    $album->reload();

    $result = $album->as_restful_array();
    $this->assert_same(rest::url("item", item::root()), $result["parent"]);
    $this->assert_same(rest::url("item", $photo), $result["album_cover"]);
    $this->assert_true(!array_key_exists("parent_id", $result));
    $this->assert_true(!array_key_exists("album_cover_item_id", $result));
  }

  public function as_restful_array_with_edit_bit_test() {
    $response = item::root()->as_restful_array(true);
    $this->assert_true($response["can_edit"]);

    identity::set_active_user(identity::guest());
    $response = item::root()->as_restful_array(true);
    $this->assert_false($response["can_edit"]);
  }

  public function first_photo_becomes_album_cover() {
    $album = test::random_album();
    $photo = test::random_photo($album);
    $album->reload();

    $this->assert_same($photo->id, $album->album_cover_item_id);
  }

  public function replace_data_file_test() {
    // Random photo is modules/gallery/tests/test.jpg which is 1024x768 and 6232 bytes.
    $photo = test::random_photo();
    $this->assert_equal(1024, $photo->width);
    $this->assert_equal(768, $photo->height);
    $this->assert_equal(6232, filesize($photo->file_path()));

    // Random photo is gallery/images/imagemagick.jpg is 114x118 and 20337 bytes
    $photo->set_data_file(MODPATH . "gallery/images/imagemagick.jpg");
    $photo->save();

    $this->assert_equal(114, $photo->width);
    $this->assert_equal(118, $photo->height);
    $this->assert_equal(20337, filesize($photo->file_path()));
  }

  public function replacement_data_file_must_be_same_mime_type_test() {
    // Random photo is modules/gallery/tests/test.jpg
    $photo = test::random_photo();
    $photo->set_data_file(MODPATH . "gallery/images/graphicsmagick.png");

    try {
      $photo->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_same(array("name" => "cant_change_mime_type"), $e->validation->errors());
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function urls_test() {
    $photo = test::random_photo();
    $this->assert_true(
      preg_match("|http://./var/resizes/name_\w+\.jpg\?m=\d+|", $photo->resize_url()),
      $photo->resize_url() . " is malformed");
    $this->assert_true(
      preg_match("|http://./var/thumbs/name_\w+\.jpg\?m=\d+|", $photo->thumb_url()),
      $photo->thumb_url() . " is malformed");
    $this->assert_true(
      preg_match("|http://./var/albums/name_\w+\.jpg\?m=\d+|", $photo->file_url()),
      $photo->file_url() . " is malformed");

    // Albums have special thumbnails.  Empty album has cachebuster of 0 since it has no thumbnail
    $album = test::random_album();
    $this->assert_true(
      preg_match("|http://./var/thumbs/name_\w+/\.album\.jpg\?m=0|", $album->thumb_url()),
      $album->thumb_url() . " is malformed");

    $photo = test::random_photo($album);
    $this->assert_true(
      preg_match("|http://./var/thumbs/name_\w+/\.album\.jpg\?m=\d+|", $album->thumb_url()),
      $album->thumb_url() . " is malformed");
  }

  public function legal_extension_test() {
    foreach (array("test.gif", "test.GIF", "test.Gif", "test.jpeg", "test.JPG") as $name) {
      $photo = test::random_photo_unsaved(item::root());
      $photo->name = $name;
      $photo->save();
    }
  }

  public function illegal_extension_test() {
    foreach (array("test.php", "test.PHP", "test.php5", "test.php4", "test.pl") as $name) {
      try {
        $photo = test::random_photo_unsaved(item::root());
        $photo->name = $name;
        $photo->save();
      } catch (ORM_Validation_Exception $e) {
        $this->assert_equal(array("name" => "illegal_data_file_extension"),
                            $e->validation->errors());
        continue;
      }
      $this->assert_true(false, "Shouldn't get here");
    }
  }
}
