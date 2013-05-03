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
class Item_Model_Test extends Unittest_TestCase {
  public function teardown() {
    Identity::set_active_user(Identity::admin_user());
  }

  public function test_saving_sets_created_and_updated_dates() {
    $item = Test::random_photo();
    $this->assertTrue(!empty($item->created));
    $this->assertTrue(!empty($item->updated));
  }

  public function test_updating_doesnt_change_created_date() {
    $item = Test::random_photo();

    // Force the creation date to something well known
    DB::update("items")
      ->set(array("created" => 0, "updated" => 0))
      ->where("id", "=", $item->id)
      ->execute();
    $item->reload();
    $item->title = "foo";  // force a change
    $item->save();

    $this->assertTrue(empty($item->created));
    $this->assertTrue(!empty($item->updated));
  }

  public function test_updating_view_count_only_doesnt_change_updated_date() {
    $item = Test::random_photo();
    $item->reload();
    $this->assertEquals(0, $item->view_count);

    // Force the updated date to something well known
    DB::update("items")
      ->set(array("updated" => 0))
      ->where("id", "=", $item->id)
      ->execute();
    $item->reload();
    $item->view_count++;
    $item->save();

    $this->assertSame(1, $item->view_count);
    $this->assertTrue(empty($item->updated));
  }

  public function test_rename_photo() {
    $item = Test::random_unique_photo();
    $original_name = $item->name;

    $thumb_file = file_get_contents($item->thumb_path());
    $resize_file = file_get_contents($item->resize_path());
    $fullsize_file = file_get_contents($item->file_path());

    // Now rename it
    $item->name = ($new_name = Test::random_name($item));
    $item->save();

    // Expected: the name changed, the name is now baked into all paths, and all files were moved.
    $this->assertEquals($new_name, $item->name);
    $this->assertEquals($new_name, basename($item->file_path()));
    $this->assertEquals($new_name, basename($item->thumb_path()));
    $this->assertEquals($new_name, basename($item->resize_path()));
    $this->assertEquals($thumb_file, file_get_contents($item->thumb_path()));
    $this->assertEquals($resize_file, file_get_contents($item->resize_path()));
    $this->assertEquals($fullsize_file, file_get_contents($item->file_path()));
  }

  public function test_rename_album() {
    $album = Test::random_album();
    $photo = Test::random_unique_photo($album);
    $album->reload();

    $thumb_file = file_get_contents($photo->thumb_path());
    $resize_file = file_get_contents($photo->resize_path());
    $fullsize_file = file_get_contents($photo->file_path());

    $original_album_name = $album->name;
    $original_photo_name = $photo->name;
    $new_album_name = Test::random_name();

    // Now rename the album
    $album->name = $new_album_name;
    $album->save();
    $photo->reload();

    // Expected:
    // * the album name changed.
    // * the album dirs are all moved
    // * the photo's paths are all inside the albums paths
    // * the photo files are all still intact and accessible
    $this->assertEquals($new_album_name, $album->name);
    $this->assertEquals($new_album_name, basename($album->file_path()));
    $this->assertEquals($new_album_name, basename(dirname($album->thumb_path())));
    $this->assertEquals($new_album_name, basename(dirname($album->resize_path())));

    $this->assertTrue(Test::starts_with($photo->file_path(), $album->file_path()));
    $this->assertTrue(Test::starts_with($photo->thumb_path(), dirname($album->thumb_path())));
    $this->assertTrue(Test::starts_with($photo->resize_path(), dirname($album->resize_path())));

    $this->assertEquals($thumb_file, file_get_contents($photo->thumb_path()));
    $this->assertEquals($resize_file, file_get_contents($photo->resize_path()));
    $this->assertEquals($fullsize_file, file_get_contents($photo->file_path()));
  }

  public function test_photo_rename_wont_accept_slash() {
    $item = Test::random_photo_unsaved();
    $item->name = "/no_slashes/allowed/";
    // Should fail on validate.
    try {
      $item->check();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("no_slashes", $errors["name"][0]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assertEquals("no_slashes_allowed.jpg", $item->name);
    // Should be corrected on update.
    $item->name = "/no_slashes/allowed/";
    $item->save();
    $this->assertEquals("no_slashes_allowed.jpg", $item->name);
  }

  public function test_photo_rename_wont_accept_backslash() {
    $item = Test::random_photo_unsaved();
    $item->name = "\\no_backslashes\\allowed\\";
    // Should fail on validate.
    try {
      $item->check();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("no_backslashes", $errors["name"][0]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assertEquals("no_backslashes_allowed.jpg", $item->name);
    // Should be corrected on update.
    $item->name = "\\no_backslashes\\allowed\\";
    $item->save();
    $this->assertEquals("no_backslashes_allowed.jpg", $item->name);
  }

  public function test_photo_rename_wont_accept_trailing_period() {
    $item = Test::random_photo_unsaved();
    $item->name = "no_trailing_period_allowed.";
    // Should fail on validate.
    try {
      $item->check();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("no_trailing_period", $errors["name"][0]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assertEquals("no_trailing_period_allowed.jpg", $item->name);
    // Should be corrected on update.
    $item->name = "no_trailing_period_allowed.";
    $item->save();
    $this->assertEquals("no_trailing_period_allowed.jpg", $item->name);
  }

  public function test_album_rename_wont_accept_slash() {
    $item = Test::random_album_unsaved();
    $item->name = "/no_album_slashes/allowed/";
    // Should fail on validate.
    try {
      $item->check();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("no_slashes", $errors["name"][0]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assertEquals("no_album_slashes_allowed", $item->name);
    // Should be corrected on update.
    $item->name = "/no_album_slashes/allowed/";
    $item->save();
    $this->assertEquals("no_album_slashes_allowed", $item->name);
  }

  public function test_album_rename_wont_accept_backslash() {
    $item = Test::random_album_unsaved();
    $item->name = "\\no_album_backslashes\\allowed\\";
    // Should fail on validate.
    try {
      $item->check();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("no_backslashes", $errors["name"][0]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assertEquals("no_album_backslashes_allowed", $item->name);
    // Should be corrected on update.
    $item->name = "\\no_album_backslashes\\allowed\\";
    $item->save();
    $this->assertEquals("no_album_backslashes_allowed", $item->name);
  }

  public function test_album_rename_wont_accept_trailing_period() {
    $item = Test::random_album_unsaved();
    $item->name = ".no_trailing_period.allowed.";
    // Should fail on validate.
    try {
      $item->check();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("no_trailing_period", $errors["name"][0]);
    }
    // Should be corrected on save.
    $item->save();
    $this->assertEquals(".no_trailing_period.allowed", $item->name);
    // Should be corrected on update.
    $item->name = ".no_trailing_period.allowed.";
    $item->save();
    $this->assertEquals(".no_trailing_period.allowed", $item->name);
  }

  public function test_move_album() {
    $album2 = Test::random_album();
    $album1 = Test::random_album($album2);
    $photo = Test::random_unique_photo($album1);

    $thumb_file = file_get_contents($photo->thumb_path());
    $resize_file = file_get_contents($photo->resize_path());
    $fullsize_file = file_get_contents($photo->file_path());

    // Now move the album
    $album1->parent_id = Item::root()->id;
    $album1->save();
    $photo->reload();

    // Expected:
    // * album is not inside album2 anymore
    // * the photo's paths are all inside the albums paths
    // * the photo files are all still intact and accessible

    $this->assertFalse(Test::starts_with($album2->file_path(), $album1->file_path()));
    $this->assertTrue(Test::starts_with($photo->file_path(), $album1->file_path()));
    $this->assertTrue(Test::starts_with($photo->thumb_path(), dirname($album1->thumb_path())));
    $this->assertTrue(Test::starts_with($photo->resize_path(), dirname($album1->resize_path())));

    $this->assertEquals($thumb_file, file_get_contents($photo->thumb_path()));
    $this->assertEquals($resize_file, file_get_contents($photo->resize_path()));
    $this->assertEquals($fullsize_file, file_get_contents($photo->file_path()));
  }

  public function test_move_photo() {
    $album1 = Test::random_album();
    $photo  = Test::random_unique_photo($album1);

    $album2 = Test::random_album();

    $thumb_file = file_get_contents($photo->thumb_path());
    $resize_file = file_get_contents($photo->resize_path());
    $fullsize_file = file_get_contents($photo->file_path());

    // Now move the photo
    $photo->parent_id = $album2->id;
    $photo->save();

    // Expected:
    // * the photo's paths are inside the album2 not album1
    // * the photo files are all still intact and accessible

    $this->assertTrue(Test::starts_with($photo->file_path(), $album2->file_path()));
    $this->assertTrue(Test::starts_with($photo->thumb_path(), dirname($album2->thumb_path())));
    $this->assertTrue(Test::starts_with($photo->resize_path(), dirname($album2->resize_path())));

    $this->assertEquals($thumb_file, file_get_contents($photo->thumb_path()));
    $this->assertEquals($resize_file, file_get_contents($photo->resize_path()));
    $this->assertEquals($fullsize_file, file_get_contents($photo->file_path()));
  }

  public function test_move_album_with_conflicting_target_gets_uniquified() {
    $album = Test::random_album();
    $source = Test::random_album_unsaved($album);
    $source->name = $album->name;
    $source->save();

    // $source and $album have the same name, so if we move $source into the root they should
    // conflict and get randomized

    $source->parent_id = Item::root()->id;
    $source->save();

    // foo should become foo-01
    $this->assertEquals("{$album->name}-01", $source->name);
    $this->assertEquals("{$album->slug}-01", $source->slug);
  }

  public function test_move_album_fails_wrong_target_type() {
    $album = Test::random_album();
    $photo = Test::random_photo();

    // $source and $album have the same name, so if we move $source into the root they should
    // conflict.

    try {
      $album->parent_id = $photo->id;
      $album->save();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("invalid", $errors["parent_id"][0]);
    }
  }

  public function test_move_photo_with_conflicting_target_gets_uniquified() {
    $photo1 = Test::random_photo();
    $album = Test::random_album();
    $photo2 = Test::random_photo_unsaved($album);
    $photo2->name = $photo1->name;
    $photo2->save();

    // $photo1 and $photo2 have the same name, so if we move $photo1 into the root they should
    // conflict and get uniquified.

    $photo2->parent_id = Item::root()->id;
    $photo2->save();

    // foo.jpg should become foo-01.jpg
    $this->assertEquals(pathinfo($photo1->name, PATHINFO_FILENAME) . "-01.jpg", $photo2->name);

    // foo should become foo-01
    $this->assertEquals("{$photo1->slug}-01", $photo2->slug);
  }

  public function test_move_album_inside_descendent_fails() {
    $album1 = Test::random_album();
    $album2 = Test::random_album($album1);
    $album3 = Test::random_album($album2);

    try {
      $album1->parent_id = $album3->id;
      $album1->save();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("invalid", $errors["parent_id"][0]);
    }
  }


  public function test_basic_validation() {
    $item = ORM::factory("Item");
    $item->album_cover_item_id = Random::int();  // invalid
    $item->description = str_repeat("x", 70000);  // invalid
    $item->name = null;
    $item->parent_id = Random::int();
    $item->slug = null;  // gets auto-generated, no error
    $item->sort_column = "bogus";
    $item->sort_order = "bogus";
    $item->title = null;  // gets auto-generated, no error
    $item->type = "bogus";
    try {
      $item->save();
      $this->assertFalse(true, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("max_length", $errors["description"][0]);
      $this->assertEquals("not_empty", $errors["name"][0]);
      $this->assertEquals("invalid_item", $errors["album_cover_item_id"][0]);
      $this->assertEquals("invalid", $errors["parent_id"][0]);
      $this->assertEquals("invalid", $errors["sort_column"][0]);
      $this->assertEquals("invalid", $errors["sort_order"][0]);
      $this->assertEquals("invalid", $errors["type"][0]);
    }
  }

  public function test_slug_is_url_safe() {
    try {
      $album = Test::random_album_unsaved();
      $album->slug = "illegal chars! !@#@#$!@~";
      $album->save();
      $this->assertTrue(false, "Shouldn't be able to save");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("not_url_safe", $errors["slug"][0]);
    }

    // This should work
    $album->slug = "the_quick_brown_fox";
    $album->save();
  }

  public function test_name_with_only_invalid_chars_is_still_valid() {
    $album = Test::random_album_unsaved();
    $album->name = "[]";
    $album->save();
  }

  public function test_cant_change_item_type() {
    $photo = Test::random_photo();
    try {
      $photo->type = "movie";
      $photo->mime_type = "video/x-flv";
      $photo->save();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $this->assertSame(
        array("name" => array("illegal_data_file_extension", null),
              "type" => array("read_only", null)),
        $e->errors());
    }
  }

  public function test_photo_files_must_have_an_extension() {
    $photo = Test::random_photo_unsaved();
    $photo->name = "no_extension_photo";
    $photo->save();
    $this->assertEquals("no_extension_photo.jpg", $photo->name);
  }

  public function test_movie_files_must_have_an_extension() {
    $movie = Test::random_movie_unsaved();
    $movie->name = "no_extension_movie";
    $movie->save();
    $this->assertEquals("no_extension_movie.flv", $movie->name);
  }

  public function test_cant_delete_root_album() {
    try {
      Item::root()->delete();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("cant_delete_root_album", $errors["id"][0]);
    }
  }

  public function test_as_restful_array() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album->reload();

    $result = $album->as_restful_array();
    $this->assertEquals(Rest::url("item", Item::root()), $result["parent"]);
    $this->assertEquals(Rest::url("item", $photo), $result["album_cover"]);
    $this->assertTrue(!array_key_exists("parent_id", $result));
    $this->assertTrue(!array_key_exists("album_cover_item_id", $result));
  }

  public function test_as_restful_array_with_edit_bit() {
    $response = Item::root()->as_restful_array();
    $this->assertTrue($response["can_edit"]);

    Access::deny(Identity::everybody(), "edit", Item::root());
    Identity::set_active_user(Identity::guest());
    $response = Item::root()->as_restful_array();
    $this->assertFalse($response["can_edit"]);
  }

  public function test_as_restful_array_with_add_bit() {
    $response = Item::root()->as_restful_array();
    $this->assertTrue($response["can_add"]);

    Access::deny(Identity::everybody(), "add", Item::root());
    Identity::set_active_user(Identity::guest());
    $response = Item::root()->as_restful_array();
    $this->assertFalse($response["can_add"]);
  }

  public function test_first_photo_becomes_album_cover() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album->reload();

    $this->assertSame($photo->id, $album->album_cover_item_id);
  }

  public function test_replace_data_file() {
    // Random photo is modules/gallery_unittest/assets/test.jpg which is 1024x768 and 6232 bytes.
    $photo = Test::random_photo();
    $this->assertEquals(1024, $photo->width);
    $this->assertEquals(768, $photo->height);
    $this->assertEquals(6232, filesize($photo->file_path()));

    // Random photo is gallery/assets/graphics/imagemagick.jpg is 114x118 and 20337 bytes
    $photo->set_data_file(MODPATH . "gallery/assets/graphics/imagemagick.jpg");
    $photo->save();

    $this->assertEquals(114, $photo->width);
    $this->assertEquals(118, $photo->height);
    $this->assertEquals(20337, filesize($photo->file_path()));
  }

  public function test_replace_data_file_type() {
    // Random photo is modules/gallery_unittest/assets/test.jpg
    $photo = Test::random_photo();
    $this->assertEquals(1024, $photo->width);
    $this->assertEquals(768, $photo->height);
    $this->assertEquals(6232, filesize($photo->file_path()));
    $this->assertEquals("image/jpeg", $photo->mime_type);
    $orig_name = $photo->name;

    // Random photo is gallery/assets/graphics/graphicsmagick.png is 104x76 and 1486 bytes
    $photo->set_data_file(MODPATH . "gallery/assets/graphics/graphicsmagick.png");
    $photo->save();

    $this->assertEquals(104, $photo->width);
    $this->assertEquals(76, $photo->height);
    $this->assertEquals(1486, filesize($photo->file_path()));
    $this->assertEquals("image/png", $photo->mime_type);
    $this->assertEquals("png", pathinfo($photo->name, PATHINFO_EXTENSION));
    $this->assertEquals(pathinfo($orig_name, PATHINFO_FILENAME), pathinfo($photo->name, PATHINFO_FILENAME));
  }

  public function test_unsafe_data_file_replacement() {
    try {
      $photo = Test::random_photo();
      $photo->set_data_file(MODPATH . "gallery/tests/Item_Model_Test.php");
      $photo->save();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("invalid_data_file", $errors["name"][0]);
    }
  }

  public function test_unsafe_data_file_replacement_with_valid_extension() {
    $temp_file = TMPPATH . "masquerading_php.jpg";
    copy(MODPATH . "gallery/tests/Item_Model_Test.php", $temp_file);
    try {
      $photo = Test::random_photo();
      $photo->set_data_file($temp_file);
      $photo->save();
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("invalid_data_file", $errors["name"][0]);
    }
  }

  public function test_urls() {
    $photo = Test::random_photo();
    $this->assertRegexp(
      "|http://localhost/var/resizes/name_\w+\.jpg\?m=\d+|", $photo->resize_url(true),
      "resize_url is malformed");
    $this->assertRegexp(
      "|http://localhost/var/thumbs/name_\w+\.jpg\?m=\d+|", $photo->thumb_url(true),
      "thumb_url is malformed");
    $this->assertRegexp(
      "|http://localhost/var/albums/name_\w+\.jpg\?m=\d+|", $photo->file_url(true),
      "file_url is malformed");

    $album = Test::random_album();
    $this->assertRegexp(
      "|http://localhost/var/thumbs/name_\w+/\.album\.jpg\?m=\d+|", $album->thumb_url(true),
      "thumb_url is malformed");

    $photo = Test::random_photo($album);
    $this->assertRegexp(
      "|http://localhost/var/thumbs/name_\w+/\.album\.jpg\?m=\d+|", $album->thumb_url(true),
      "thumb url is malformed");

    // If the file does not exist, we should return a cache buster of m=0.
    unlink($album->thumb_path());
    $this->assertRegexp(
      "|http://localhost/var/thumbs/name_\w+/\.album\.jpg\?m=0|", $album->thumb_url(true),
      "thumb url is malformed");
  }

  public function test_legal_extension_that_does_match_gets_used() {
    foreach (array("jpg", "JPG", "Jpg", "jpeg") as $extension) {
      $photo = Test::random_photo_unsaved(Item::root());
      $photo->name = Test::random_name() . ".{$extension}";
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assertEquals($extension, pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function test_illegal_extension() {
    foreach (array("test.php", "test.PHP", "test.php5", "test.php4",
                   "test.pl", "test.php.png") as $name) {
      $photo = Test::random_photo_unsaved(Item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assertEquals("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function test_cant_rename_to_illegal_extension() {
    foreach (array("test.php.test", "test.php", "test.PHP",
                   "test.php5", "test.php4", "test.pl") as $name) {
      $photo = Test::random_photo(Item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assertEquals("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function test_legal_extension_that_doesnt_match_gets_fixed() {
    foreach (array("test.png", "test.mp4", "test.GIF") as $name) {
      $photo = Test::random_photo_unsaved(Item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assertEquals("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function test_rename_to_legal_extension_that_doesnt_match_gets_fixed() {
    foreach (array("test.png", "test.mp4", "test.GIF") as $name) {
      $photo = Test::random_photo(Item::root());
      $photo->name = $name;
      $photo->save();
      // Should get renamed with the correct jpg extension of the data file.
      $this->assertEquals("jpg", pathinfo($photo->name, PATHINFO_EXTENSION));
    }
  }

  public function test_albums_can_have_two_dots_in_name() {
    $album = Test::random_album_unsaved(Item::root());
    $album->name = $album->name . ".foo.bar";
    $album->save();
  }

  public function test_no_conflict_when_parents_different() {
    $parent1 = Test::random_album();
    $parent2 = Test::random_album();
    $photo1 = Test::random_photo($parent1);
    $photo2 = Test::random_photo($parent2);

    $photo2->name = $photo1->name;
    $photo2->slug = $photo1->slug;
    $photo2->save();

    // photo2 has same name and slug as photo1 but different parent - no conflict.
    $this->assertEquals($photo1->name, $photo2->name);
    $this->assertEquals($photo1->slug, $photo2->slug);
  }

  public function test_fix_conflict_when_names_identical() {
    $parent = Test::random_album();
    $photo1 = Test::random_photo($parent);
    $photo2 = Test::random_photo($parent);

    $photo1_orig_base = pathinfo($photo1->name, PATHINFO_FILENAME);
    $photo2_orig_slug = $photo2->slug;

    $photo2->name = $photo1->name;
    $photo2->save();

    // photo2 has same name as photo1 - conflict resolved by renaming with -01.
    $this->assertEquals("{$photo1_orig_base}-01.jpg", $photo2->name);
    $this->assertEquals("{$photo2_orig_slug}-01", $photo2->slug);
  }

  public function test_fix_conflict_when_slugs_identical() {
    $parent = Test::random_album();
    $photo1 = Test::random_photo($parent);
    $photo2 = Test::random_photo($parent);

    $photo2_orig_base = pathinfo($photo2->name, PATHINFO_FILENAME);

    $photo2->slug = $photo1->slug;
    $photo2->save();

    // photo2 has same slug as photo1 - conflict resolved by renaming with -01.
    $this->assertEquals("{$photo2_orig_base}-01.jpg", $photo2->name);
    $this->assertEquals("{$photo1->slug}-01", $photo2->slug);
  }

  public function test_no_conflict_when_parents_different_for_albums() {
    $parent1 = Test::random_album();
    $parent2 = Test::random_album();
    $album1 = Test::random_album($parent1);
    $album2 = Test::random_album($parent2);

    $album2->name = $album1->name;
    $album2->slug = $album1->slug;
    $album2->save();

    // album2 has same name and slug as album1 but different parent - no conflict.
    $this->assertEquals($album1->name, $album2->name);
    $this->assertEquals($album1->slug, $album2->slug);
  }

  public function test_fix_conflict_when_names_identical_for_albums() {
    $parent = Test::random_album();
    $album1 = Test::random_album($parent);
    $album2 = Test::random_album($parent);

    $album2_orig_slug = $album2->slug;

    $album2->name = $album1->name;
    $album2->save();

    // album2 has same name as album1 - conflict resolved by renaming with -01.
    $this->assertEquals("{$album1->name}-01", $album2->name);
    $this->assertEquals("{$album2_orig_slug}-01", $album2->slug);
  }

  public function test_fix_conflict_when_slugs_identical_for_albums() {
    $parent = Test::random_album();
    $album1 = Test::random_album($parent);
    $album2 = Test::random_album($parent);

    $album2_orig_name = $album2->name;

    $album2->slug = $album1->slug;
    $album2->save();

    // album2 has same slug as album1 - conflict resolved by renaming with -01.
    $this->assertEquals("{$album2_orig_name}-01", $album2->name);
    $this->assertEquals("{$album1->slug}-01", $album2->slug);
  }

  public function test_no_conflict_when_base_names_identical_between_album_and_photo() {
    $parent = Test::random_album();
    $album = Test::random_album($parent);
    $photo = Test::random_photo($parent);

    $photo_orig_slug = $photo->slug;

    $photo->name = "{$album->name}.jpg";
    $photo->save();

    // photo has same base name as album - no conflict.
    $this->assertEquals("{$album->name}.jpg", $photo->name);
    $this->assertEquals($photo_orig_slug, $photo->slug);
  }

  public function test_fix_conflict_when_full_names_identical_between_album_and_photo() {
    $parent = Test::random_album();
    $photo = Test::random_photo($parent);
    $album = Test::random_album($parent);

    $album_orig_slug = $album->slug;

    $album->name = $photo->name;
    $album->save();

    // album has same full name as album - conflict resolved by renaming with -01.
    $this->assertEquals("{$photo->name}-01", $album->name);
    $this->assertEquals("{$album_orig_slug}-01", $album->slug);
  }

  public function test_fix_conflict_when_slugs_identical_between_album_and_photo() {
    $parent = Test::random_album();
    $album = Test::random_album($parent);
    $photo = Test::random_photo($parent);

    $photo_orig_base = pathinfo($photo->name, PATHINFO_FILENAME);

    $photo->slug = $album->slug;
    $photo->save();

    // photo has same slug as album - conflict resolved by renaming with -01.
    $this->assertEquals("{$photo_orig_base}-01.jpg", $photo->name);
    $this->assertEquals("{$album->slug}-01", $photo->slug);
  }

  public function test_fix_conflict_when_base_names_identical_between_jpg_png_flv() {
    $parent = Test::random_album();
    $item1 = Test::random_photo($parent);
    $item2 = Test::random_photo($parent);
    $item3 = Test::random_movie($parent);

    $item1_orig_base = pathinfo($item1->name, PATHINFO_FILENAME);
    $item2_orig_slug = $item2->slug;
    $item3_orig_slug = $item3->slug;

    $item2->set_data_file(MODPATH . "gallery/assets/graphics/graphicsmagick.png");
    $item2->name = "{$item1_orig_base}.png";
    $item2->save();

    $item3->name = "{$item1_orig_base}.flv";
    $item3->save();

    // item2 and item3 have same base name as item1 - conflict resolved by renaming with -01 and -02.
    $this->assertEquals("{$item1_orig_base}-01.png", $item2->name);
    $this->assertEquals("{$item2_orig_slug}-01", $item2->slug);
    $this->assertEquals("{$item1_orig_base}-02.flv", $item3->name);
    $this->assertEquals("{$item3_orig_slug}-02", $item3->slug);
  }

  public function test_children_default_to_albums_sort_order() {
    $album = Test::random_album_unsaved();
    $album->sort_column = "name";
    $album->sort_order = "DESC";
    $album->save();

    $album->children->find_all();
    $this->assertRegexp("/ORDER BY `item`.`name` DESC, `item`.`id` ASC/", Database::instance()->last_query);
  }

  public function test_descendants_default_to_albums_sort_order() {
    $album = Test::random_album_unsaved();
    $album->sort_column = "view_count";
    $album->sort_order = "DESC";
    $album->save();

    $album->descendants->find_all();
    $this->assertRegexp("/ORDER BY `item`.`view_count` DESC, `item`.`id` ASC/", Database::instance()->last_query);
  }
}
