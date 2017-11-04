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

    if ($form->validate()) {
      // Uploadify puts the result in $_FILES["Filedata"] - process it.
      try {
        list ($tmp_name, $name) = $this->_process_upload("Filedata");
      } catch (Exception $e) {
        header("HTTP/1.1 400 Bad Request");
        print "ERROR: " . $e->getMessage();
        return;
      }

      // We have a valid upload file (of unknown type) - build an item from it.
      try {
        $item = $this->_add_item($id, $tmp_name, $name);
        module::event("add_photos_form_completed", $item, $form);
        print "FILEID: $item->id";
      } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        print "ERROR: " . $e->getMessage();
      }
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
      print t2("Uploaded %count photo", "Uploaded %count photos", $success_count);
    }
  }

  public function finish() {
    access::verify_csrf();
    batch::stop();
    json::reply(array("result" => "success"));
  }

  private function _get_add_form($album) {
    $form = new Forge("uploader/finish", "", "post", array("id" => "gAddPhotosForm", 'class' => 'dropzone'));
    $group = $form->group("add_photos")
      ->label(t("Add photos to %album_title", array("album_title" => html::purify($album->title))));
    $group->dropzone("dropzone")->album($album);

    $group_actions = $form->group("actions");
    $group_actions->dropzone_buttons("");

    $inputs_before_event = array_keys($form->add_photos->inputs);
    module::event("add_photos_form", $album, $form);
    $inputs_after_event = array_keys($form->add_photos->inputs);

    return $form;
  }

  private function _get_add_form_uploadify($album) {
    $form = new Forge("uploader/finish", "", "post", array("id" => "g-add-photos-form"));
    $group = $form->group("add_photos")
      ->label(t("Add photos to %album_title", array("album_title" => html::purify($album->title))));
    $group->uploadify("uploadify")->album($album);

    $group_actions = $form->group("actions");
    $group_actions->uploadify_buttons("");

    $inputs_before_event = array_keys($form->add_photos->inputs);
    module::event("add_photos_form", $album, $form);
    $inputs_after_event = array_keys($form->add_photos->inputs);

    // For each new input in add_photos, attach JS to make uploadify update its value.
    foreach (array_diff($inputs_after_event, $inputs_before_event) as $input) {
      if (!$input) {
        // Likely a script input - don't do anything with it.
        continue;
      }
      $group->uploadify->script_data($input, $group->{$input}->value);
      $group->script("")
        ->text("$('input[name=\"$input\"]').change(function (event) {
                  $('#g-uploadify').uploadifySettings('scriptData', {'$input': $(this).val()});
                });");
    }

    return $form;
  }

  /**
   * Process the uploaded file.  This handles the interface with Kohana's upload and validation
   * code, and marks the new temp file for deletion on shutdown.  It returns the temp file path
   * (tmp_name) and filename (name), analogous to their respective $_FILES elements.
   * If the upload is invalid, it will throw an exception.  Note that no type-checking (e.g. jpg,
   * mp4,...) is performed here.
   * @TODO: consider moving this to a common controller which is extended by various uploaders.
   *
   * @param  string name of $_FILES input
   * @return array  array($tmp_name, $name)
   */
  private function _process_upload($file) {
    // Validate file data.  At this point, any file extension is still valid.
    $file_validation = new Validation($_FILES);
    $file_validation->add_rules($file, "upload::valid",  "upload::required");
    if (!$file_validation->validate()) {
      throw new Exception(t("Invalid upload"));
    }

    // Save temp file and mark for deletion when done.
    $tmp_name = upload::save($file);
    system::delete_later($tmp_name);

    // Get uploaded filename.  This is different than tmp_name since it hasn't been uniquified.
    $name = $_FILES[$file]["name"];

    return array($tmp_name, $name);
  }

  /**
   * Add photo or movie from upload.  Once we have a valid file, this generates an item model
   * from it.  It returns the item model on success, and throws an exception and adds log entries
   * on failure.
   * @TODO: consider moving this to a common controller which is extended by various uploaders.
   *
   * @param  int    parent album id
   * @param  string temp file path (analogous to $_FILES[...]["tmp_name"])
   * @param  string filename       (analogous to $_FILES[...]["name"])
   * @return object new item model
   */
  private function _add_item($album_id, $tmp_name, $name) {
    $extension = pathinfo($name, PATHINFO_EXTENSION);

    try {
      $item = ORM::factory("item");
      $item->name = $name;
      $item->title = item::convert_filename_to_title($name);
      $item->parent_id = $album_id;
      $item->set_data_file($tmp_name);

      if (!$extension) {
        throw new Exception(t("Uploaded file has no extension"));
      } else if (legal_file::get_photo_extensions($extension)) {
        $item->type = "photo";
        $item->save();
        log::success("content", t("Added a photo"),
                     html::anchor("photos/$item->id", t("view photo")));
      } else if (movie::allow_uploads() && legal_file::get_movie_extensions($extension)) {
        $item->type = "movie";
        $item->save();
        log::success("content", t("Added a movie"),
                     html::anchor("movies/$item->id", t("view movie")));
      } else {
        throw new Exception(t("Uploaded file has illegal extension"));
      }
    } catch (Exception $e) {
      // Log errors then re-throw exception.
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());

      // If we have a validation error, add an additional log entry.
      if ($e instanceof ORM_Validation_Exception) {
        Kohana_Log::add("error", "Validation errors: " . print_r($e->validation->errors(), 1));
      }

      throw $e;
    }

    return $item;
  }
}
