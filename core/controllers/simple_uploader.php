<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
    $v->flash_vars =
      "uploadUrl=" . urlencode(
        url::site("simple_uploader/add_photo/$item->id" .
                  "?csrf=" . access::csrf_token() .
                  "&g3sid=" . Session::instance()->id() .
                  "&user_agent=" . Input::instance()->server("HTTP_USER_AGENT"))) .
      "&title=" . urlencode(t("Add photos")) .
      "&addLabel=" . urlencode(t("Choose photos to add..."));
    print $v;
  }

  public function add_photo($id) {
    $album = ORM::factory("item", $id);
    access::required("edit", $album);
    access::verify_csrf();

    $file_validation = new Validation($_FILES);
    $file_validation->add_rules("file", "upload::valid", "upload::type[gif,jpg,png]");
    if ($file_validation->validate()) {
      $temp_filename  = upload::save("file");
      $title = substr(basename($temp_filename), 10);  // Skip unique identifier Kohana adds
      $photo = photo::create(
        $album,
        $temp_filename,
        $title,
        $title);
      log::success("content", "Added a photo", html::anchor("photos/$photo->id", "view photo"));
    }
  }
}
