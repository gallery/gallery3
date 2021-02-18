<?php defined("SYSPATH") or die("No direct script access.");

class movie_resized_event_Core {
  static function movie_img ($movie_img, $obj) {
    $resize_file = $obj->resize_path().'.mp4';

    if (file_exists($resize_file)) {
      $resize_url = $obj->resize_url(true);
      $relative_file = $obj->relative_path();

      $movie_img->url = str_replace($relative_file, $relative_file.'.mp4', $resize_url);
    }
  }
}
