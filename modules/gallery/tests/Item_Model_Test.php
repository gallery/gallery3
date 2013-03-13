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
    $item = test::random_unique_photo();
    $original_name = $item->name;

    $thumb_file = file_get_contents($item->thumb_path());
    $resize_file = file_get_contents($item->resize_path());
    $fullsize_file = file_get_contents($item->file_path());

    // Now rename it
    $item->name = ($new_name = test::random_name($item));
    $item->save();

    // Expected: the name changed, the name is now baked into all paths, and all files were moved.
    $this->assert_equal($new_name, $item->name);
    $this->assert_equal($new_name, basename($item->file_path()));
    $this->assert_equal($new_name, basename($item->thumb_path()));
    $this->assert_equal($new_name, basename($item->resize_path()));
    $this->assert_equal($thumb_file, file_get_contents($item->thumb_path()));
    $this->assert_equal($resize_file, file_get_contents($item->resize_path()));
    $this->assert_equal($fullsize_file, file_get_contents($item->file_path()));
  }

  public function rename_album_test() {
    $album = test::random_album();
    $photo = test::random_unique_photo($album);
    $album->reload();

    $thumb_file = file_get_contents($photo->thumb_path());
    $resize_file = file_get_contents($photo->resize_path());
    $fullsize_file = file_get_contents($photo->file_path());

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

    $this->assert_equal($thumb_file, file_get_contents($photo->thumb_path()));
    $this->assert_equal($resize_file, file_get_contents($photo->resize_path()));
    $this->assert_equal($fullsize_file, file_get_contents($photo->file_path()));
  }

  public function photo_rename_wont_accept_slash_test() {
    $item = test::random_photo_unsaved();
    $item->name = "/no_slashes/allowed/";
    // Should fail on validate.
    try {
      $item->validate();
      $this->assert_true(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->validation->errors();
      $this->assert_same("no_slashes", $errors["name"]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assert_equal("no_slashes_allowed.jpg", $item->name);
    // Should be corrected on update.
    $item->name = "/no_slashes/allowed/";
    $item->save();
    $this->assert_equal("no_slashes_allowed.jpg", $item->name);
  }

  public function photo_rename_wont_accept_backslash_test() {
    $item = test::random_photo_unsaved();
    $item->name = "\\no_backslashes\\allowed\\";
    // Should fail on validate.
    try {
      $item->validate();
      $this->assert_true(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->validation->errors();
      $this->assert_same("no_backslashes", $errors["name"]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assert_equal("no_backslashes_allowed.jpg", $item->name);
    // Should be corrected on update.
    $item->name = "\\no_backslashes\\allowed\\";
    $item->save();
    $this->assert_equal("no_backslashes_allowed.jpg", $item->name);
  }

  public function photo_rename_wont_accept_trailing_period_test() {
    $item = test::random_photo_unsaved();
    $item->name = "no_trailing_period_allowed.";
    // Should fail on validate.
    try {
      $item->validate();
      $this->assert_true(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->validation->errors();
      $this->assert_same("no_trailing_period", $errors["name"]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assert_equal("no_trailing_period_allowed.jpg", $item->name);
    // Should be corrected on update.
    $item->name = "no_trailing_period_allowed.";
    $item->save();
    $this->assert_equal("no_trailing_period_allowed.jpg", $item->name);
  }

  public function album_rename_wont_accept_slash_test() {
    $item = test::random_album_unsaved();
    $item->name = "/no_album_slashes/allowed/";
    // Should fail on validate.
    try {
      $item->validate();
      $this->assert_true(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->validation->errors();
      $this->assert_same("no_slashes", $errors["name"]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assert_equal("no_album_slashes_allowed", $item->name);
    // Should be corrected on update.
    $item->name = "/no_album_slashes/allowed/";
    $item->save();
    $this->assert_equal("no_album_slashes_allowed", $item->name);
  }

  public function album_rename_wont_accept_backslash_test() {
    $item = test::random_album_unsaved();
    $item->name = "\\no_album_backslashes\\allowed\\";
    // Should fail on validate.
    try {
      $item->validate();
      $this->assert_true(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->validation->errors();
      $this->assert_same("no_backslashes", $errors["name"]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assert_equal("no_album_backslashes_allowed", $item->name);
    // Should be corrected on update.
    $item->name = "\\no_album_backslashes\\allowed\\";
    $item->save();
    $this->assert_equal("no_album_backslashes_allowed", $item->name);
  }

  public function album_rename_wont_accept_trailing_period_test() {
    $item = test::random_album_unsaved();
    $item->name = ".no_trailing_period.allowed.";
    // Should fail on validate.
    try {
      $item->validate();
      $this->assert_true(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->validation->errors();
      $this->assert_same("no_trailing_period", $errors["name"]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assert_equal(".no_trailing_period.allowed", $item->name);
    // Should be corrected on update.
    $item->name = ".no_trailing_period.allowed.";
    $item->save();
    $this->assert_equal(".no_trailing_period.allowed", $item->name);
  }

  public function move_album_test() {
    $album2 = test::random_album();
    $album1 = test::random_album($album2);
    $photo = test::random_unique_photo($album1);

    $thumb_file = file_get_contents($photo->thumb_path());
    $resize_file = file_get_contents($photo->resize_path());
    $fullsize_file = file_get_contents($photo->file_path());

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

    $this->assert_equal($thumb_file, file_get_contents($photo->thumb_path()));
    $this->assert_equal($resize_file, file_get_contents($photo->resize_path()));
    $this->assert_equal($fullsize_file, file_get_contents($photo->file_path()));
  }

  public function move_photo_test() {
    $album1 = test::random_album();
    $photo  = test::random_unique_photo($album1);

    $album2 = test::random_album();

    $thumb_file = file_get_contents($photo->thumb_path());
    $resize_file = file_get_contents($photo->resize_path());
    $fullsize_file = file_get_contents($photo->file_path());

    // Now move the photo
    $photo->parent_id = $album2->id;
    $photo->save();

    // Expected:
    // * the photo's paths are inside the album2 not album1
    // * the photo files are all still intact and accessible

    $this->assert_true(test::starts_with($photo->file_path(), $album2->file_path()));
    $this->assert_true(test::starts_with($photo->thumb_path(), dirname($album2->thumb_path())));
    $this->assert_true(test::starts_with($photo->resize_path(), dirname($album2->resize_path())));

    $this->assert_equal($thumb_file, file_get_contents($photo->thumb_path()));
    $this->assert_equal($resize_file, file_get_contents($photo->resize_path()));
    $this->assert_equal($fullsize_file, file_get_contents($photo->file_path()));
  }

  public function move_album_with_conflicting_target_gets_uniquified_test() {
    $album = test::random_album();
    $source = test::random_album_unsaved($album);
    $source->name = $album->name;
    $source->save();

    // $source and $album have the same name, so if we move $source into the root they should
    // conflict and get randomized

    $source->parent_id = item::root()->id;
    $source->save();

    // foo should become foo-01
    $this->assert_same("{$album->name}-01", $source->name);
    $this->assert_same("{$album->slug}-01", $source->slug);
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

  public function move_photo_with_conflicting_target_gets_uniquified_test() {
    $photo1 = test::random_photo();
    $album = test::random_album();
    $photo2 = test::random_photo_unsaved($album);
    $photo2->name = $photo1->name;
    $photo2->save();

    // $photo1 and $photo2 have the same name, so if we move $photo1 into the root they should
    // conflict and get uniquified.

    $photo2->parent_id = item::root()->id;
    $photo2->save();

    // foo.jpg should become foo-01.jpg
    $this->assert_same(pathinfo($photo1->name, PATHINFO_FILENAME) . "-01.jpg", $photo2->name);

    // foo should become foo-01
    $this->assert_same("{$photo1->slug}-01", $photo2->slug);
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
      $this->assert_same(
        array("name" => "illegal_data_file_extension", "type" => "read_only"),
        $e->validation->errors());
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function photo_files_must_have_an_extension_test() {
    $photo = test::random_photo_unsaved();
    $photo->name = "no_extension_photo";
    $photo->save();
    $this->assert_equal("no_extension_photo.jpg", $photo->name);
  }

  public function movie_files_must_have_an_extension_test() {
    $movie = test::random_movie_unsaved();
    $movie->name = "no_extension_movie";
    $movie->save();
    $this->assert_equal("no_extension_movie.flv", $movie->name);
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
    $response = item::root()->as_restful_array();
    $this->assert_true($response["can_edit"]);

    access::deny(identity::everybody(), "edit", item::root());
    identity::set_active_user(identity::guest());
    $response = item::root()->as_restful_array();
    $this->assert_false($response["can_edit"]);
  }

  public function as_restful_array_with_add_bit_test() {
    $response = item::root()->as_restful_array();
    $this->assert_true($response["can_add"]);

    access::deny(identity::everybody(), "add", item::root());
    identity::set_active_user(identity::guest());
    $response = item::root()->as_restful_array();
    $this->assert_false($response["can_add"]);
  }

  public function first_photo_becomes_album_cover_test() {
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

  public function replace_data_file_type_test() {
    // Random photo is modules/gallery/tests/test.jpg
    $photo = test::random_photo();
    $this->assert_equal(1024, $photo->width);
    $this->assert_equal(768, $photo->height);
    $this->assert_equal(6232, filesize($photo->file_path()));
    $this->assert_equal("image/jpeg", $photo->mime_type);
    $orig_name = $photo->name;

    // Random photo is gallery/images/graphicsmagick.png is 104x76 and 1486 bytes
    $photo->set_data_file(MODPATH . "gallery/images/graphicsmagick.png");
    $photo->save();

    $this->assert_equal(104, $photo->width);
    $this->assert_equal(76, $photo->height);
    $this->assert_equal(1486, filesize($photo->file_path()));
    $this->assert_equal("image/png", $photo->mime_type);
    $this->assert_equal("png", pathinfo($photo->name, PATHINFO_EXTENSION));
    $this->assert_equal(pathinfo($orig_name, PATHINFO_FILENAME), pathinfo($photo->name, PATHINFO_FILENAME));
  }

  public function unsafe_data_file_replacement_test() {
    try {
      $photo = test::random_photo();
      $photo->set_data_file(MODPATH . "gallery/tests/Item_Model_Test.php");
      $photo->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_same(array("name" => "invalid_data_file"), $e->validation->errors());
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function unsafe_data_file_replacement_with_valid_extension_test() {
    $temp_file = TMPPATH . "masquerading_php.jpg";
    copy(MODPATH . "gallery/tests/Item_Model_Test.php", $temp_file);
    try {
      $photo = test::random_photo();
      $photo->set_data_file($temp_file);
      $photo->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_same(array("name" => "invalid_data_file"), $e->validation->errors());
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

    $album = test::random_album();
    $this->assert_true(
      preg_match("|http://./var/thumbs/name_\w+/\.album\.jpg\?m=\d+|", $album->thumb_url()),
      $album->thumb_url() . " is malformed");

    $photo = test::random_photo($album);
    $this->assert_true(
      preg_match("|http://./var/thumbs/name_\w+/\.album\.jpg\?m=\d+|", $album->thumb_url()),
      $album->thumb_url() . " is malformed");

    // If the file does not exist, we should return a cache buster of m=0.
    unlink($album->thumb_path());
    $this->assert_true(
      preg_match("|http://./var/thumbs/name_\w+/\.album\.jpg\?m=0|", $album->thumb_url()),
      $album->thumb_url() . " is malformed");
  }

  public function legal_extension_that_does_match_gets_used_test() {
    foreach (array("jpg", "JPG", "Jpg", "jpeg") as $extension) {
      $photo = test::random_photo_unsaved(item::root());
      $photo->name = test::random_name() . ".{$extension}";
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assert_equal($extension, pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function illegal_extension_test() {
    foreach (array("test.php", "test.PHP", "test.php5", "test.php4",
                   "test.pl", "test.php.png") as $name) {
      $photo = test::random_photo_unsaved(item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assert_equal("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function cant_rename_to_illegal_extension_test() {
    foreach (array("test.php.test", "test.php", "test.PHP",
                   "test.php5", "test.php4", "test.pl") as $name) {
      $photo = test::random_photo(item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assert_equal("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function legal_extension_that_doesnt_match_gets_fixed_test() {
    foreach (array("test.png", "test.mp4", "test.GIF") as $name) {
      $photo = test::random_photo_unsaved(item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assert_equal("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function rename_to_legal_extension_that_doesnt_match_gets_fixed_test() {
    foreach (array("test.png", "test.mp4", "test.GIF") as $name) {
      $photo = test::random_photo(item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assert_equal("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function albums_can_have_two_dots_in_name_test() {
    $album = test::random_album_unsaved(item::root());
    $album->name = $album->name . ".foo.bar";
    $album->save();
  }

  public function no_conflict_when_parents_different_test() {
    $parent1 = test::random_album();
    $parent2 = test::random_album();
    $photo1 = test::random_photo($parent1);
    $photo2 = test::random_photo($parent2);

    $photo2->name = $photo1->name;
    $photo2->slug = $photo1->slug;
    $photo2->save();

    // photo2 has same name and slug as photo1 but different parent - no conflict.
    $this->assert_same($photo1->name, $photo2->name);
    $this->assert_same($photo1->slug, $photo2->slug);
  }

  public function fix_conflict_when_names_identical_test() {
    $parent = test::random_album();
    $photo1 = test::random_photo($parent);
    $photo2 = test::random_photo($parent);

    $photo1_orig_base = pathinfo($photo1->name, PATHINFO_FILENAME);
    $photo2_orig_slug = $photo2->slug;

    $photo2->name = $photo1->name;
    $photo2->save();

    // photo2 has same name as photo1 - conflict resolved by renaming with -01.
    $this->assert_same("{$photo1_orig_base}-01.jpg", $photo2->name);
    $this->assert_same("{$photo2_orig_slug}-01", $photo2->slug);
  }

  public function fix_conflict_when_slugs_identical_test() {
    $parent = test::random_album();
    $photo1 = test::random_photo($parent);
    $photo2 = test::random_photo($parent);

    $photo2_orig_base = pathinfo($photo2->name, PATHINFO_FILENAME);

    $photo2->slug = $photo1->slug;
    $photo2->save();

    // photo2 has same slug as photo1 - conflict resolved by renaming with -01.
    $this->assert_same("{$photo2_orig_base}-01.jpg", $photo2->name);
    $this->assert_same("{$photo1->slug}-01", $photo2->slug);
  }

  public function no_conflict_when_parents_different_for_albums_test() {
    $parent1 = test::random_album();
    $parent2 = test::random_album();
    $album1 = test::random_album($parent1);
    $album2 = test::random_album($parent2);

    $album2->name = $album1->name;
    $album2->slug = $album1->slug;
    $album2->save();

    // album2 has same name and slug as album1 but different parent - no conflict.
    $this->assert_same($album1->name, $album2->name);
    $this->assert_same($album1->slug, $album2->slug);
  }

  public function fix_conflict_when_names_identical_for_albums_test() {
    $parent = test::random_album();
    $album1 = test::random_album($parent);
    $album2 = test::random_album($parent);

    $album2_orig_slug = $album2->slug;

    $album2->name = $album1->name;
    $album2->save();

    // album2 has same name as album1 - conflict resolved by renaming with -01.
    $this->assert_same("{$album1->name}-01", $album2->name);
    $this->assert_same("{$album2_orig_slug}-01", $album2->slug);
  }

  public function fix_conflict_when_slugs_identical_for_albums_test() {
    $parent = test::random_album();
    $album1 = test::random_album($parent);
    $album2 = test::random_album($parent);

    $album2_orig_name = $album2->name;

    $album2->slug = $album1->slug;
    $album2->save();

    // album2 has same slug as album1 - conflict resolved by renaming with -01.
    $this->assert_same("{$album2_orig_name}-01", $album2->name);
    $this->assert_same("{$album1->slug}-01", $album2->slug);
  }

  public function no_conflict_when_base_names_identical_between_album_and_photo_test() {
    $parent = test::random_album();
    $album = test::random_album($parent);
    $photo = test::random_photo($parent);

    $photo_orig_slug = $photo->slug;

    $photo->name = "{$album->name}.jpg";
    $photo->save();

    // photo has same base name as album - no conflict.
    $this->assert_same("{$album->name}.jpg", $photo->name);
    $this->assert_same($photo_orig_slug, $photo->slug);
  }

  public function fix_conflict_when_full_names_identical_between_album_and_photo_test() {
    $parent = test::random_album();
    $photo = test::random_photo($parent);
    $album = test::random_album($parent);

    $album_orig_slug = $album->slug;

    $album->name = $photo->name;
    $album->save();

    // album has same full name as album - conflict resolved by renaming with -01.
    $this->assert_same("{$photo->name}-01", $album->name);
    $this->assert_same("{$album_orig_slug}-01", $album->slug);
  }

  public function fix_conflict_when_slugs_identical_between_album_and_photo_test() {
    $parent = test::random_album();
    $album = test::random_album($parent);
    $photo = test::random_photo($parent);

    $photo_orig_base = pathinfo($photo->name, PATHINFO_FILENAME);

    $photo->slug = $album->slug;
    $photo->save();

    // photo has same slug as album - conflict resolved by renaming with -01.
    $this->assert_same("{$photo_orig_base}-01.jpg", $photo->name);
    $this->assert_same("{$album->slug}-01", $photo->slug);
  }

  public function fix_conflict_when_base_names_identical_between_jpg_png_flv_test() {
    $parent = test::random_album();
    $item1 = test::random_photo($parent);
    $item2 = test::random_photo($parent);
    $item3 = test::random_movie($parent);

    $item1_orig_base = pathinfo($item1->name, PATHINFO_FILENAME);
    $item2_orig_slug = $item2->slug;
    $item3_orig_slug = $item3->slug;

    $item2->set_data_file(MODPATH . "gallery/images/graphicsmagick.png");
    $item2->name = "{$item1_orig_base}.png";
    $item2->save();

    $item3->name = "{$item1_orig_base}.flv";
    $item3->save();

    // item2 and item3 have same base name as item1 - conflict resolved by renaming with -01 and -02.
    $this->assert_same("{$item1_orig_base}-01.png", $item2->name);
    $this->assert_same("{$item2_orig_slug}-01", $item2->slug);
    $this->assert_same("{$item1_orig_base}-02.flv", $item3->name);
    $this->assert_same("{$item3_orig_slug}-02", $item3->slug);
  }
}
