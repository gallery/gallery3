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
class Item_Test extends Unittest_TestCase {
  public function teardown() {
    Identity::set_active_user(Identity::admin_user());
    parent::teardown();
  }

  public function test_viewable() {
    $album = Test::random_album();
    $item = Test::random_photo($album);
    $album->reload();
    Identity::set_active_user(Identity::guest());

    // We can see the item when permissions are granted
    Access::allow(Identity::everybody(), "view", $album);
    $this->assertEquals(
      1,
      ORM::factory("Item")->viewable()->where("id", "=", $item->id)->count_all());

    // We can't see the item when permissions are denied
    Access::deny(Identity::everybody(), "view", $album);
    $this->assertEquals(
      0,
      ORM::factory("Item")->viewable()->where("id", "=", $item->id)->count_all());
  }

  public function test_convert_filename_to_title() {
    $this->assertEquals("foo", Item::convert_filename_to_title("foo.jpg"));
    $this->assertEquals("foo.bar", Item::convert_filename_to_title("foo.bar.jpg"));
  }

  public function test_convert_filename_to_slug() {
    $this->assertEquals("foo", Item::convert_filename_to_slug("{[foo]}"));
    $this->assertEquals("foo-bar", Item::convert_filename_to_slug("{[foo!@#!$@#^$@($!(@bar]}"));
    $this->assertEquals("english-text", Item::convert_filename_to_slug("english text"));
    $this->assertEquals("new-line", Item::convert_filename_to_slug("new \n line"));
    $this->assertEquals("foo-and-bar", Item::convert_filename_to_slug("foo&bar"));
    $this->assertEquals("special", Item::convert_filename_to_slug("šṗëçîąļ"));
  }

  public function test_move() {
    $photo = Test::random_photo(Item::root());
    $dst_album = Test::random_album();

    Item::move($photo, $dst_album);
    $this->assertSame($dst_album->id, $photo->parent_id);
  }

  public function test_move_updates_album_covers() {
    // 2 photos in the source album
    $src_album = Test::random_album();
    $photo1 = Test::random_photo($src_album);
    $photo2 = Test::random_photo($src_album);
    $src_album->reload();

    // destination album
    $dst_album = Test::random_album();

    Item::move($photo1, $dst_album);

    // Refresh cached copies
    $src_album->reload();
    $dst_album->reload();

    // photo 2 becomes the album cover for the source album and photo 1
    // becomes the album cover for the destination
    $this->assertSame($photo1->id, $dst_album->album_cover_item_id);
    $this->assertSame($photo2->id, $src_album->album_cover_item_id);
  }

  public function test_move_leaves_empty_album_with_no_album_cover() {
    $src_album = Test::random_album();
    $photo = Test::random_photo($src_album);

    Item::move($photo, Item::root());

    $src_album->reload();
    $this->assertNull($src_album->album_cover_item_id);
  }

  public function test_move_conflicts_result_in_a_rename() {
    $rand = Random::int();
    $photo1 = Test::random_photo_unsaved(Item::root());
    $photo1->name = "{$rand}.jpg";
    $photo1->slug = (string)$rand;
    $photo1->save();

    $src_album = Test::random_album();
    $photo2 = Test::random_photo_unsaved($src_album);
    $photo2->name = "{$rand}.jpg";
    $photo2->slug = (string)$rand;
    $photo2->save();

    Item::move($photo2, Item::root());

    $this->assertSame(Item::root()->id, $photo2->parent_id);
    $this->assertNotSame("{$rand}.jpg", $photo2->name);
    $this->assertNotSame($rand, $photo2->slug);
  }

  public function test_delete_cover_photo_picks_new_album_cover() {
    $parent = Test::random_album();
    $album = Test::random_album($parent);
    $photo1 = Test::random_photo($album);
    // At this point, $photo1 is the album cover.  We verify this in
    // Item_Model_Test::first_photo_becomes_album_cover
    $photo2 = Test::random_photo($album);
    $photo1->delete();
    $album->reload();
    $parent->reload();

    $this->assertSame($photo2->id, $album->album_cover_item_id);
    $this->assertSame($photo2->id, $parent->album_cover_item_id);
  }

  public function test_find_by_path() {
    $level1 = Test::random_album();
    $level2 = Test::random_album_unsaved($level1);
    $level2->name = "plus + space";
    $level2->save()->reload();

    $level3 = Test::random_photo_unsaved($level2);
    $level3->name = "same.jpg";
    $level3->save()->reload();

    $level2b = Test::random_album($level1);
    $level3b = Test::random_photo_unsaved($level2b);
    $level3b->name = "same.jpg";
    $level3b->save()->reload();

    // Item in album
    $this->assertSame(
      $level3->id,
      Item::find_by_path("/{$level1->name}/{$level2->name}/{$level3->name}")->id);

    // Album, ends with a slash
    $this->assertSame(
      $level2->id,
      Item::find_by_path("{$level1->name}/{$level2->name}/")->id);

    // Album, ends without a slash
    $this->assertSame(
      $level2->id,
      Item::find_by_path("/{$level1->name}/{$level2->name}")->id);

    // Return root if "" is passed
    $this->assertSame(Item::root()->id, Item::find_by_path("")->id);

    // Verify that we don't get confused by the part names, using the fallback code.
    self::_remove_relative_path_caches();
    self::_remove_relative_path_caches();

    $this->assertSame(
      $level3->id,
      Item::find_by_path("{$level1->name}/{$level2->name}/{$level3->name}")->id);

    $this->assertSame(
      $level3b->id,
      Item::find_by_path("{$level1->name}/{$level2b->name}/{$level3b->name}")->id);

    // Verify that we don't get false positives
    $this->assertFalse(
      Item::find_by_path("foo/bar/baz")->loaded());
  }

  public function test_find_by_path_with_jpg() {
    $parent = Test::random_album();
    $jpg = Test::random_photo($parent);

    $jpg_path = "{$parent->name}/{$jpg->name}";
    $flv_path = LegalFile::change_extension($jpg_path, "flv");

    // Check normal operation.
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path, "albums")->id);
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path, "resizes")->id);
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path, "thumbs")->id);
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path)->id);

    // Check that we don't get false positives.
    $this->assertEquals(null, Item::find_by_path($flv_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path, "resizes")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path, "resizes")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($jpg->id, Item::find_by_path($jpg_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals(null, Item::find_by_path($flv_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path, "resizes")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path)->id);
  }

  public function test_find_by_path_with_png() {
    $parent = Test::random_album();
    $png = Test::random_photo_unsaved($parent);
    $png->set_data_file(MODPATH . "gallery/assets/graphics/graphicsmagick.png");
    $png->save();

    $png_path = "{$parent->name}/{$png->name}";
    $jpg_path = LegalFile::change_extension($png_path, "jpg");

    // Check normal operation.
    $this->assertEquals($png->id, Item::find_by_path($png_path, "albums")->id);
    $this->assertEquals($png->id, Item::find_by_path($png_path, "resizes")->id);
    $this->assertEquals($png->id, Item::find_by_path($png_path, "thumbs")->id);
    $this->assertEquals($png->id, Item::find_by_path($png_path)->id);

    // Check that we don't get false positives.
    $this->assertEquals(null, Item::find_by_path($jpg_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path, "resizes")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals($png->id, Item::find_by_path($png_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($png->id, Item::find_by_path($png_path, "resizes")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($png->id, Item::find_by_path($png_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($png->id, Item::find_by_path($png_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals(null, Item::find_by_path($jpg_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path, "resizes")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path)->id);
  }

  public function test_find_by_path_with_flv() {
    $parent = Test::random_album();
    $flv = Test::random_movie($parent);

    $flv_path = "{$parent->name}/{$flv->name}";
    $jpg_path = LegalFile::change_extension($flv_path, "jpg");

    // Check normal operation.
    $this->assertEquals($flv->id, Item::find_by_path($flv_path, "albums")->id);
    $this->assertEquals($flv->id, Item::find_by_path($jpg_path, "thumbs")->id);
    $this->assertEquals($flv->id, Item::find_by_path($flv_path)->id);

    // Check that we don't get false positives.
    $this->assertEquals(null, Item::find_by_path($jpg_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals($flv->id, Item::find_by_path($flv_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($flv->id, Item::find_by_path($jpg_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($flv->id, Item::find_by_path($flv_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals(null, Item::find_by_path($jpg_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($flv_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($jpg_path)->id);
  }

  public function test_find_by_path_with_album() {
    $parent = Test::random_album();
    $album = Test::random_movie($parent);

    $album_path = "{$parent->name}/{$album->name}";
    $thumb_path = "{$album_path}/.album.jpg";

    // Check normal operation.
    $this->assertEquals($album->id, Item::find_by_path($album_path, "albums")->id);
    $this->assertEquals($album->id, Item::find_by_path($thumb_path, "thumbs")->id);
    $this->assertEquals($album->id, Item::find_by_path($album_path)->id);

    // Check that we don't get false positives.
    $this->assertEquals(null, Item::find_by_path($thumb_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($album_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($thumb_path)->id);

    // Check normal operation without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals($album->id, Item::find_by_path($album_path, "albums")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($album->id, Item::find_by_path($thumb_path, "thumbs")->id);
    self::_remove_relative_path_caches();
    $this->assertEquals($album->id, Item::find_by_path($album_path)->id);

    // Check that we don't get false positives without relative path cache.
    self::_remove_relative_path_caches();
    $this->assertEquals(null, Item::find_by_path($thumb_path, "albums")->id);
    $this->assertEquals(null, Item::find_by_path($album_path, "thumbs")->id);
    $this->assertEquals(null, Item::find_by_path($thumb_path)->id);
  }

  protected function _remove_relative_path_caches() {
    // This gets used *many* times in the find_by_path tests above to check the fallback code.
    DB::update("items")
      ->set(array("relative_path_cache" => null))
      ->execute();
  }

  public function test_find_by_relative_url() {
    $level1 = Test::random_album();
    $level2 = Test::random_album($level1);
    $level3 = Test::random_photo_unsaved($level2);
    $level3->slug = "same";
    $level3->save()->reload();

    $level2b = Test::random_album($level1);
    $level3b = Test::random_photo_unsaved($level2b);
    $level3b->slug = "same";
    $level3b->save()->reload();

    // Item in album
    $this->assertSame(
      $level3->id,
      Item::find_by_relative_url("{$level1->slug}/{$level2->slug}/{$level3->slug}")->id);

    // Album, ends without a slash
    $this->assertSame(
      $level2->id,
      Item::find_by_relative_url("{$level1->slug}/{$level2->slug}")->id);

    // Return root if "" is passed
    $this->assertSame(Item::root()->id, Item::find_by_relative_url("")->id);

    // Verify that we don't get confused by the part slugs, using the fallback code.
    DB::update("items")
      ->set(array("relative_url_cache" => null))
      ->where("id", "IN", array($level3->id, $level3b->id))
      ->execute();
    $this->assertSame(
      $level3->id,
      Item::find_by_relative_url("{$level1->slug}/{$level2->slug}/{$level3->slug}")->id);

    $this->assertSame(
      $level3b->id,
      Item::find_by_relative_url("{$level1->slug}/{$level2b->slug}/{$level3b->slug}")->id);

    // Verify that we don't get false positives
    $this->assertFalse(
      Item::find_by_relative_url("foo/bar/baz")->loaded());

    // Verify that the fallback code works
    $this->assertSame(
      $level3b->id,
      Item::find_by_relative_url("{$level1->slug}/{$level2b->slug}/{$level3b->slug}")->id);
  }

  public function test_resequence_child_weights() {
    $album = Test::random_album_unsaved();
    $album->sort_column = "id";
    $album->save();

    $photo1 = Test::random_photo($album);
    $photo2 = Test::random_photo($album);
    $this->assertTrue($photo2->weight > $photo1->weight);

    $album->reload();
    $album->sort_order = "DESC";
    $album->save();
    Item::resequence_child_weights($album);

    $this->assertEquals(2, $photo1->reload()->weight);
    $this->assertEquals(1, $photo2->reload()->weight);
  }
}
