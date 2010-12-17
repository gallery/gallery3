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
class test_Core {
  static function random_album_unsaved($parent=null) {
    $rand = random::string(6);

    $album = ORM::factory("item");
    $album->type = "album";
    $album->parent_id = $parent ? $parent->id : 1;
    $album->name = "name_$rand";
    $album->title = "title_$rand";
    return $album;
  }

  static function random_album($parent=null) {
    return test::random_album_unsaved($parent)->save()->reload();
  }

  static function random_photo_unsaved($parent=null) {
    $rand = random::string(6);
    $photo = ORM::factory("item");
    $photo->type = "photo";
    $photo->parent_id = $parent ? $parent->id : 1;
    $photo->set_data_file(MODPATH . "gallery/tests/test.jpg");
    $photo->name = "name_$rand.jpg";
    $photo->title = "title_$rand";
    return $photo;
  }

  static function random_photo($parent=null) {
    return test::random_photo_unsaved($parent)->save()->reload();
  }

  static function random_user($password="password") {
    $rand = "name_" . random::string(6);
    return identity::create_user($rand, $rand, $password, "$rand@rand.com");
  }

  static function random_group() {
    return identity::create_group(random::string(6));
  }

  static function random_name($item=null) {
    $rand = "name_" . random::string(6);
    if ($item && $item->is_photo()) {
      $rand .= ".jpg";
    }
    return $rand;
  }

  static function starts_with($outer, $inner) {
    return strpos($outer, $inner) === 0;
  }

  static function call_and_capture($callback) {
    ob_start();
    call_user_func($callback);
    return ob_get_clean();
  }

  static function random_tag() {
    $tag = ORM::factory("tag");
    $tag->name = random::string(6);

    // Reload so that ORM coerces all fields into strings.
    return $tag->save()->reload();
  }

  static function diff($a, $b) {
    fwrite(fopen($a_name = tempnam("/tmp", "test"), "w"), $a);
    fwrite(fopen($b_name = tempnam("/tmp", "test"), "w"), $b);
    return `diff $a_name $b_name`;
  }
}
