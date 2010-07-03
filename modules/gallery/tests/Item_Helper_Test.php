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
    $rand = rand();
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
}
