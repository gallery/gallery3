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
class Item_Helper_Test extends Gallery_Unit_Test_Case {
  public function teardown() {
    identity::set_active_user(identity::admin_user());
  }

  public function viewable_test() {
    $album = test::random_album();
    $item = test::random_photo($album);
    $album->reload();
    identity::set_active_user(identity::guest());

    // We can see the item when permissions are granted
    access::allow(identity::everybody(), "view", $album);
    $this->assert_equal(
      1,
      ORM::factory("item")->viewable()->where("id", "=", $item->id)->count_all());

    // We can't see the item when permissions are denied
    access::deny(identity::everybody(), "view", $album);
    $this->assert_equal(
      0,
      ORM::factory("item")->viewable()->where("id", "=", $item->id)->count_all());
  }

  public function convert_filename_to_title_test() {
    $this->assert_equal("foo", item::convert_filename_to_title("foo.jpg"));
    $this->assert_equal("foo.bar", item::convert_filename_to_title("foo.bar.jpg"));
  }

  public function convert_filename_to_slug_test() {
    $this->assert_equal("foo", item::convert_filename_to_slug("{[foo]}"));
    $this->assert_equal("foo-bar", item::convert_filename_to_slug("{[foo!@#!$@#^$@($!(@bar]}"));
    $this->assert_equal("english-text", item::convert_filename_to_slug("english text"));
    $this->assert_equal("new-line", item::convert_filename_to_slug("new \n line"));
    $this->assert_equal("foo-and-bar", item::convert_filename_to_slug("foo&bar"));
    $this->assert_equal("special", item::convert_filename_to_slug("šṗëçîąļ"));
  }

  public function move_test() {
    $photo = test::random_photo(item::root());
    $dst_album = test::random_album();

    item::move($photo, $dst_album);
    $this->assert_same($dst_album->id, $photo->parent_id);
  }

  public function move_updates_album_covers_test() {
    // 2 photos in the source album
    $src_album = test::random_album();
    $photo1 = test::random_photo($src_album);
    $photo2 = test::random_photo($src_album);
    $src_album->reload();

    // destination album
    $dst_album = test::random_album();

    item::move($photo1, $dst_album);

    // Refresh cached copies
    $src_album->reload();
    $dst_album->reload();

    // photo 2 becomes the album cover for the source album and photo 1
    // becomes the album cover for the destination
    $this->assert_same($photo1->id, $dst_album->album_cover_item_id);
    $this->assert_same($photo2->id, $src_album->album_cover_item_id);
  }

  public function move_leaves_empty_album_with_no_album_cover_test() {
    $src_album = test::random_album();
    $photo = test::random_photo($src_album);

    item::move($photo, item::root());

    $src_album->reload();
    $this->assert_false($src_album->album_cover_item_id);
  }

  public function move_conflicts_result_in_a_rename_test() {
    $rand = random::int();
    $photo1 = test::random_photo_unsaved(item::root());
    $photo1->name = "{$rand}.jpg";
    $photo1->slug = (string)$rand;
    $photo1->save();

    $src_album = test::random_album();
    $photo2 = test::random_photo_unsaved($src_album);
    $photo2->name = "{$rand}.jpg";
    $photo2->slug = (string)$rand;
    $photo2->save();

    item::move($photo2, item::root());

    $this->assert_same(item::root()->id, $photo2->parent_id);
    $this->assert_not_same("{$rand}.jpg", $photo2->name);
    $this->assert_not_same($rand, $photo2->slug);
  }

  public function delete_cover_photo_picks_new_album_cover_test() {
    $parent = test::random_album();
    $album = test::random_album($parent);
    $photo1 = test::random_photo($album);
    // At this point, $photo1 is the album cover.  We verify this in
    // Item_Model_Test::first_photo_becomes_album_cover
    $photo2 = test::random_photo($album);
    $photo1->delete();
    $album->reload();
    $parent->reload();

    $this->assert_same($photo2->id, $album->album_cover_item_id);
    $this->assert_same($photo2->id, $parent->album_cover_item_id);
  }

  public function find_by_path_test() {
    $level1 = test::random_album();
    $level2 = test::random_album_unsaved($level1);
    $level2->name = "plus + space";
    $level2->save()->reload();

    $level3 = test::random_photo_unsaved($level2);
    $level3->name = "same.jpg";
    $level3->save()->reload();

    $level2b = test::random_album($level1);
    $level3b = test::random_photo_unsaved($level2b);
    $level3b->name = "same.jpg";
    $level3b->save()->reload();

    // Item in album
    $this->assert_same(
      $level3->id,
      item::find_by_path("/{$level1->name}/{$level2->name}/{$level3->name}")->id);

    // Album, ends with a slash
    $this->assert_same(
      $level2->id,
      item::find_by_path("{$level1->name}/{$level2->name}/")->id);

    // Album, ends without a slash
    $this->assert_same(
      $level2->id,
      item::find_by_path("/{$level1->name}/{$level2->name}")->id);

    // Return root if "" is passed
    $this->assert_same(item::root()->id, item::find_by_path("")->id);

    // Verify that we don't get confused by the part names, using the fallback code.
    self::_remove_relative_path_caches();
    self::_remove_relative_path_caches();

    $this->assert_same(
      $level3->id,
      item::find_by_path("{$level1->name}/{$level2->name}/{$level3->name}")->id);

    $this->assert_same(
      $level3b->id,
      item::find_by_path("{$level1->name}/{$level2b->name}/{$level3b->name}")->id);

    // Verify that we don't get false positives
    $this->assert_false(
      item::find_by_path("foo/bar/baz")->loaded());
  }

  public function find_by_path_with_jpg_test() {
    $parent = test::random_album();
    $jpg = test::random_photo($parent);

    $jpg_path = "{$parent->name}/{$jpg->name}";
    $flv_path = legal_file::change_extension($jpg_path, "flv");

    // Check normal operation.
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path, "albums")->id);
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path, "resizes")->id);
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path, "thumbs")->id);
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path)->id);

    // Check that we don't get false positives.
    $this->assert_equal(null, item::find_by_path($flv_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($flv_path, "resizes")->id);
    $this->assert_equal(null, item::find_by_path($flv_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($flv_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path, "resizes")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($jpg->id, item::find_by_path($jpg_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal(null, item::find_by_path($flv_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($flv_path, "resizes")->id);
    $this->assert_equal(null, item::find_by_path($flv_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($flv_path)->id);
  }

  public function find_by_path_with_png_test() {
    $parent = test::random_album();
    $png = test::random_photo_unsaved($parent);
    $png->set_data_file(MODPATH . "gallery/images/graphicsmagick.png");
    $png->save();

    $png_path = "{$parent->name}/{$png->name}";
    $jpg_path = legal_file::change_extension($png_path, "jpg");

    // Check normal operation.
    $this->assert_equal($png->id, item::find_by_path($png_path, "albums")->id);
    $this->assert_equal($png->id, item::find_by_path($png_path, "resizes")->id);
    $this->assert_equal($png->id, item::find_by_path($png_path, "thumbs")->id);
    $this->assert_equal($png->id, item::find_by_path($png_path)->id);

    // Check that we don't get false positives.
    $this->assert_equal(null, item::find_by_path($jpg_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path, "resizes")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal($png->id, item::find_by_path($png_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($png->id, item::find_by_path($png_path, "resizes")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($png->id, item::find_by_path($png_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($png->id, item::find_by_path($png_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal(null, item::find_by_path($jpg_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path, "resizes")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path)->id);
  }

  public function find_by_path_with_flv_test() {
    $parent = test::random_album();
    $flv = test::random_movie($parent);

    $flv_path = "{$parent->name}/{$flv->name}";
    $jpg_path = legal_file::change_extension($flv_path, "jpg");

    // Check normal operation.
    $this->assert_equal($flv->id, item::find_by_path($flv_path, "albums")->id);
    $this->assert_equal($flv->id, item::find_by_path($jpg_path, "thumbs")->id);
    $this->assert_equal($flv->id, item::find_by_path($flv_path)->id);

    // Check that we don't get false positives.
    $this->assert_equal(null, item::find_by_path($jpg_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($flv_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal($flv->id, item::find_by_path($flv_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($flv->id, item::find_by_path($jpg_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($flv->id, item::find_by_path($flv_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal(null, item::find_by_path($jpg_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($flv_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($jpg_path)->id);
  }

  public function find_by_path_with_album_test() {
    $parent = test::random_album();
    $album = test::random_movie($parent);

    $album_path = "{$parent->name}/{$album->name}";
    $thumb_path = "{$album_path}/.album.jpg";

    // Check normal operation.
    $this->assert_equal($album->id, item::find_by_path($album_path, "albums")->id);
    $this->assert_equal($album->id, item::find_by_path($thumb_path, "thumbs")->id);
    $this->assert_equal($album->id, item::find_by_path($album_path)->id);

    // Check that we don't get false positives.
    $this->assert_equal(null, item::find_by_path($thumb_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($album_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($thumb_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal($album->id, item::find_by_path($album_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($album->id, item::find_by_path($thumb_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assert_equal($album->id, item::find_by_path($album_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assert_equal(null, item::find_by_path($thumb_path, "albums")->id);
    $this->assert_equal(null, item::find_by_path($album_path, "thumbs")->id);
    $this->assert_equal(null, item::find_by_path($thumb_path)->id);
  }

  private function _remove_relative_path_caches() {
    // This gets used *many* times in the find_by_path tests above to check the fallback code.
    db::build()
      ->update("items")
      ->set("relative_path_cache", null)
      ->execute();
  }

  public function find_by_relative_url_test() {
    $level1 = test::random_album();
    $level2 = test::random_album($level1);
    $level3 = test::random_photo_unsaved($level2);
    $level3->slug = "same";
    $level3->save()->reload();

    $level2b = test::random_album($level1);
    $level3b = test::random_photo_unsaved($level2b);
    $level3b->slug = "same";
    $level3b->save()->reload();

    // Item in album
    $this->assert_same(
      $level3->id,
      item::find_by_relative_url("{$level1->slug}/{$level2->slug}/{$level3->slug}")->id);

    // Album, ends without a slash
    $this->assert_same(
      $level2->id,
      item::find_by_relative_url("{$level1->slug}/{$level2->slug}")->id);

    // Return root if "" is passed
    $this->assert_same(item::root()->id, item::find_by_relative_url("")->id);

    // Verify that we don't get confused by the part slugs, using the fallback code.
    db::build()
      ->update("items")
      ->set(array("relative_url_cache" => null))
      ->where("id", "IN", array($level3->id, $level3b->id))
      ->execute();
    $this->assert_same(
      $level3->id,
      item::find_by_relative_url("{$level1->slug}/{$level2->slug}/{$level3->slug}")->id);

    $this->assert_same(
      $level3b->id,
      item::find_by_relative_url("{$level1->slug}/{$level2b->slug}/{$level3b->slug}")->id);

    // Verify that we don't get false positives
    $this->assert_false(
      item::find_by_relative_url("foo/bar/baz")->loaded());

    // Verify that the fallback code works
    $this->assert_same(
      $level3b->id,
      item::find_by_relative_url("{$level1->slug}/{$level2b->slug}/{$level3b->slug}")->id);
  }

  public function resequence_child_weights_test() {
    $album = test::random_album_unsaved();
    $album->sort_column = "id";
    $album->save();

    $photo1 = test::random_photo($album);
    $photo2 = test::random_photo($album);
    $this->assert_true($photo2->weight > $photo1->weight);

    $album->reload();
    $album->sort_order = "DESC";
    $album->save();
    item::resequence_child_weights($album);

    $this->assert_equal(2, $photo1->reload()->weight);
    $this->assert_equal(1, $photo2->reload()->weight);
  }
}
