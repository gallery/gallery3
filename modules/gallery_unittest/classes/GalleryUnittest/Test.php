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
class GalleryUnittest_Test {
  static function random_album_unsaved($parent=null) {
    $rand = Test::random_string(6);

    $album = ORM::factory("Item");
    $album->type = "album";
    $album->parent_id = $parent ? $parent->id : Item::root()->id;
    $album->name = "name_$rand";
    $album->title = "title_$rand";
    return $album;
  }

  static function random_album($parent=null) {
    return Test::random_album_unsaved($parent)->save()->reload();
  }

  static function random_movie_unsaved($parent=null) {
    $rand = Test::random_string(6);
    $movie = ORM::factory("Item");
    $movie->type = "movie";
    $movie->parent_id = $parent ? $parent->id : Item::root()->id;
    $movie->set_data_file(MODPATH . "gallery_unittest/assets/test.flv");
    $movie->name = "name_$rand.flv";
    $movie->title = "title_$rand";
    return $movie;
  }

  static function random_movie($parent=null) {
    return Test::random_movie_unsaved($parent)->save()->reload();
  }

  static function random_photo_unsaved($parent=null) {
    $rand = Test::random_string(6);
    $photo = ORM::factory("Item");
    $photo->type = "photo";
    $photo->parent_id = $parent ? $parent->id : Item::root()->id;
    $photo->set_data_file(MODPATH . "gallery_unittest/assets/test.jpg");
    $photo->name = "name_$rand.jpg";
    $photo->title = "title_$rand";
    return $photo;
  }

  static function random_photo($parent=null) {
    return Test::random_photo_unsaved($parent)->save()->reload();
  }

  // If a test compares photo file contents (i.e. file_get_contents), it's best to use this
  // function to guarantee uniqueness.
  static function random_unique_photo_unsaved($parent=null) {
    $rand = Test::random_string(6);
    $photo = ORM::factory("Item");
    $photo->type = "photo";
    $photo->parent_id = $parent ? $parent->id : Item::root()->id;
    if (function_exists("gd_info")) {
      // Make image unique - color the black dot of test.jpg to the 6-digit hex code of rand.
      $image = imagecreatefromjpeg(MODPATH . "gallery_unittest/assets/test.jpg");
      imagefilter($image, IMG_FILTER_COLORIZE,
        hexdec(substr($rand, 0, 2)), hexdec(substr($rand, 2, 2)), hexdec(substr($rand, 4, 2)));
      imagejpeg($image, TMPPATH . "test_$rand.jpg");
      imagedestroy($image);
      $photo->set_data_file(TMPPATH . "test_$rand.jpg");
    } else {
      // Just use the black dot.
      $photo->set_data_file(MODPATH . "gallery_unittest/assets/test.jpg");
    }
    $photo->name = "name_$rand.jpg";
    $photo->title = "title_$rand";
    return $photo;
  }

  static function random_unique_photo($parent=null) {
    return Test::random_unique_photo_unsaved($parent)->save()->reload();
  }

  static function random_user($password="password") {
    $rand = "name_" . Test::random_string(6);
    return Identity::create_user($rand, $rand, $password, "$rand@rand.com");
  }

  static function random_group() {
    return Identity::create_group(Test::random_string(6));
  }

  static function random_name($item=null) {
    $rand = "name_" . Test::random_string(6);
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
    $tag = ORM::factory("Tag");
    $tag->name = trim(str_replace(",", ".", Test::lorem_ipsum(rand(2, 4))));

    // Reload so that ORM coerces all fields into strings.
    return $tag->save()->reload();
  }

  static function random_comment_unsaved($item=null, $user=null) {
    $comment = ORM::factory("Comment");
    $comment->author_id = $user ? $user->id : Identity::active_user()->id;
    $comment->item_id = $item ? $item->id : Item::root()->id;
    $comment->text = Test::lorem_ipsum(rand(3, 10));
    if ($comment->author_id == Identity::guest()->id) {
      $comment->guest_name = Test::random_string(6);
      $comment->guest_email = "{$comment->guest_name}@example.com";
      $comment->guest_url = "http://www.example.com";
    }
    return $comment;
  }

  static function random_comment($item=null, $user=null) {
    return Test::random_comment_unsaved($item, $user)->save()->reload();
  }

  static function diff($a, $b) {
    fwrite(fopen($a_name = tempnam("/tmp", "test"), "w"), is_array($a) ? implode("\n", $a) : $a);
    fwrite(fopen($b_name = tempnam("/tmp", "test"), "w"), is_array($b) ? implode("\n", $b) : $b);
    return `diff $a_name $b_name`;
  }

  static function random_string($length) {
    $buf = "";
    do {
      $buf .= Random::hash();
    } while (strlen($buf) < $length);
    return substr($buf, 0, $length);
  }

  static function lorem_ipsum($num) {
    static $lorem_ipsum = null;
    if (!$lorem_ipsum) {
      require_once(MODPATH . "gallery_unittest/vendor/LoremIpsum.class.php");
      $lorem_ipsum = new LoremIpsumGenerator();
    }
    // skip past initial 'lorem ipsum'
    return substr($lorem_ipsum->getContent($num + 2, "txt"), 13);
  }
}
