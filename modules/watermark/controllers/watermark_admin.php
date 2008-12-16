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
class Watermark_admin_Controller extends Controller {
  public function load() {
    $form = watermark::get_watermark_form();
    Kohana::log("debug", print_r($form, 1));
    if ($form->validate()) {
        $file = $_POST["file"];
        Kohana::log("debug", $file);

        $pathinfo = pathinfo($file);
        $watermark_target = $pathinfo["basename"];
        if (copy($file, VARPATH . $watermark_target)) {
          module::set_var("watermark", "watermark_image_path", $watermark_target);
          unlink($file);
          $form->success = _("Watermark saved");
        } else {
          // @todo set and error message
        }
    }

    print $form;
  }

  public function get_form($user_id) {
    try {
      // @todo check for admin user

      $path = module::get_var("watermark", "watermark_image_path");
      $view = new View("watermark_position.html");

      if (empty($path)) {
        // @todo need to do something when there is no watermark
      }

      $photo = ORM::factory("item")
        ->where("type", "photo")
        ->find_all(1, 0)->current();

      // @todo determine what to do if water mark is not set
      // @todo caclulate the view sizes
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