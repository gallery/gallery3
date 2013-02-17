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
class Uploader_Controller extends Controller {
  public function index($id) {
    $item = ORM::factory("item", $id);
    access::required("view", $item);
    access::required("add", $item);
    if (!$item->is_album()) {
      $item = $item->parent();
    }

    print $this->_get_add_form($item);
  }

  public function start() {
    access::verify_csrf();
    batch::start();
  }

  public function add_photo($id) {
    $album = ORM::factory("item", $id);
    access::required("view", $album);
    access::required("add", $album);
    access::verify_csrf();

    // The Flash uploader not call /start directly, so simulate it here for now.
    if (!batch::in_progress()) {
      batch::start();
    }

    $form = $this->_get_add_form($album);

    // Uploadify adds its own field to the form, so validate that separately.
    $file_validation = new Validation($_FILES);
    $file_validation->add_rules(
      "Filedata", "upload::valid",  "upload::required",
      "upload::type[" . implode(",", legal_file::get_extensions()) . "]");

    if ($form->validate() && $file_validation->validate()) {
      $temp_filename = upload::save("Filedata");
      system::delete_later($temp_filename);
      try {
        $item = ORM::factory("item");
        $item->name = substr(basename($temp_filename), 10);  // Skip unique identifier Kohana adds
        $item->title = item::convert_filename_to_title($item->name);
        $item->parent_id = $album->id;
        $item->set_data_file($temp_filename);

        $path_info = @pathinfo($temp_filename);
        if (array_key_exists("extension", $path_info) &&
            legal_file::get_movie_extensions($path_info["extension"])) {
          $item->type = "movie";
          $item->save();
          log::success("content", t("Added a movie"),
                       html::anchor("movies/$item->id", t("view movie")));
        } else {
          $item->type = "photo";
          $item->save();
          log::success("content", t("Added a photo"),
                       html::anchor("photos/$item->id", t("view photo")));
        }

        module::event("add_photos_form_completed", $item, $form);
      } catch (Exception $e) {
        // The Flash uploader has no good way of reporting complex errors, so just keep it simple.
        Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());

        // Ugh.  I hate to use instanceof, But this beats catching the exception separately since
        // we mostly want to treat it the same way as all other exceptions
        if ($e instanceof ORM_Validation_Exception) {
          Kohana_Log::add("error", "Validation errors: " . print_r($e->validation->errors(), 1));
        }

        header("HTTP/1.1 500 Internal Server Error");
        print "ERROR: " . $e->getMessage();
        return;
      }
      print "FILEID: $item->id";
    } else {
      header("HTTP/1.1 400 Bad Request");
      print "ERROR: " . t("Invalid upload");
    }
  }

  public function status($success_count, $error_count) {
    if ($error_count) {
      // The "errors" won't be properly pluralized :-/
      print t2("Uploaded %count photo (%error errors)",
               "Uploaded %count photos (%error errors)",
               (int)$success_count,
               array("error" => (int)$error_count));
    } else {
      print t2("Uploaded %count photo", "Uploaded %count photos", $success_count);}
  }

  public function finish() {
    access::verify_csrf();

    batch::stop();
    json::reply(array("result" => "success"));
  }

  private function _get_add_form($album)  {
    $form = new Forge("uploader/finish", "", "post", array("id" => "g-add-photos-form"));
    $group = $form->group("add_photos")
      ->label(t("Add photos to %album_title", array("album_title" => html::purify($album->title))));
    $group->uploadify("uploadify")->album($album);

    $group = $form->group("actions");
    $group->uploadify_buttons("");

    module::event("add_photos_form", $album, $form);

    return $form;
  }
}
