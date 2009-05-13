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
class Simple_Uploader_Controller extends Controller {
  public function app($id) {
    $item = ORM::factory("item", $id);
    access::required("edit", $item);

    $v = new View("simple_uploader.html");
    $v->item = $item;
    print $v;
  }

  public function start() {
    batch::start();
  }

  public function add_photo($id) {
    $album = ORM::factory("item", $id);
    access::required("add", $album);
    access::verify_csrf();

    $file_validation = new Validation($_FILES);
    $file_validation->add_rules("Filedata", "upload::valid", "upload::type[gif,jpg,png,flv,mp4]");
    if ($file_validation->validate()) {

      // SimpleUploader.swf does not yet call /start directly, so simulate it here for now.
      if (!batch::in_progress()) {
        batch::start();
      }

      $temp_filename = upload::save("Filedata");
      try {
        $title = substr(basename($temp_filename), 10);  // Skip unique identifier Kohana adds
        $title = $this->convert_filename_to_title($title);
        $path_info = pathinfo($temp_filename);
        if (array_key_exists("extension", $path_info) &&
            in_array(strtolower($path_info["extension"]), array("flv", "mp4"))) {
          $movie = movie::create($album, $temp_filename, $title, $title);
          log::success("content", t("Added a movie"),
                       html::anchor("movies/$movie->id", t("view movie")));
        } else {
          $photo = photo::create($album, $temp_filename, $title, $title);
          log::success("content", t("Added a photo"),
                       html::anchor("photos/$photo->id", t("view photo")));
        }
      } catch (Exception $e) {
        unlink($temp_filename);
        throw $e;
      }
      unlink($temp_filename);
    }
    print "File Received";
  }

  /**
   * We should move this into a helper somewhere.. but where is appropriate?
   */
  private function convert_filename_to_title($filename) {
    $title = strtr($filename, "_", " ");
    $title = preg_replace("/\..*?$/", "", $title);
    $title = preg_replace("/ +/", " ", $title);
    return $title;
  }

  public function finish() {
    batch::stop();
    print json_encode(array("result" => "success"));
  }
}
