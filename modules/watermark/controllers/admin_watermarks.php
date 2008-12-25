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
class Admin_Watermarks_Controller extends Admin_Controller {
  public function index() {
    $form = watermark::get_watermark_form();
    if (request::method() == "post" && $form->validate()) {
      $file = $_POST["file"];
      $pathinfo = pathinfo($file);
      $name = preg_replace("/uploadfile-[^-]+-(.*)/", '$1', $pathinfo["basename"]);
      if (ORM::factory("watermark")->where("name", $name)->count_all() > 0) {
        message::add(_("There is already a watermark with that name"), log::WARNING);
      } else if (!($image_info = getimagesize($file))) {
        message::add(_("An error occurred while saving this watermark"), log::WARNING);
      } else {
        if (empty($pathinfo["extension"])) {
          $name .= "." . image_type_to_extension($image_info[2]);
        }
        if (!rename($file, VARPATH . "modules/watermark/$name")) {
          message::add(_("An error occurred while saving this watermark"), log::WARNING);
        } else {
          $watermark = ORM::factory("watermark");
          $watermark->name = $name;
          $watermark->width = $image_info[0];
          $watermark->height = $image_info[1];
          $watermark->mime_type = $image_info["mime"];
          $watermark->save();

          message::add(_("Watermark saved"));
          url::redirect("admin/watermarks");
        }
      }
      @unlink($file);
    }

    $view = new Admin_View("admin.html");
    $view->content = new View("admin_watermarks.html");
    $view->content->watermarks = ORM::factory("watermark")->find_all();
    $view->content->form = watermark::get_watermark_form();
    print $view;
  }

  public function edit($watermark_id) {
  }

  public function delete($watermark_id) {
  }

  public function get_form($user_id) {
    try {
      $path = module::get_var("watermark", "watermark_image_path");
      $view = new View("watermark_position.html");

      if (empty($path)) {
        // @todo need to do something when there is no watermark
      }

      $photo = ORM::factory("item")
        ->where("type", "photo")
        ->find_all(1, 0)->current();

      // @todo determine what to do if water mark is not set
      // @todo calculate the view sizes
      $view->sample_image =  $photo->resize_url();
      $scaleWidth = $photo->resize_width / $photo->width;
      $scaleHeight = $photo->resize_height / $photo->height;
      $scale = $scaleHeight < $scaleWidth ? $scaleHeight : $scaleWidth;

      $imageinfo = getimagesize(VARPATH . $path);

      $view->watermark_height = $imageinfo[1] * $scale;
      $view->watermark_width = $imageinfo[0] * $scale;
      $view->watermark_image = url::abs_file("var/" . $path);

      $current_position = module::get_var("watermark", "watermark_position");
      $view->watermark_position_form = watermark::get_watermark_postion_form($current_position);

      print $view;
    } catch (Exception $e) {
      print $e;
    }
  }
}