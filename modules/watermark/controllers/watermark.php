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
class Watermark_Controller extends Controller {
  public function load() {
    $form = new View("watermark_add_form.html");
    $form->errors = $form->fields = array("file" => "");

    if ($_FILES) {
      $post = Validation::factory(array_merge($_POST, $_FILES))
        ->add_rules("file", "upload::valid", "upload::type[gif,jpg,png]", "upload::size[1M]");

      if ($post->validate()) {
        $file = upload::save("file");
        Kohana::log("debug", $file);
        $form->success = _("Watermark saved");
      } else {
        $form->fields = arr::overwrite($form->fields, $post->as_array());
        $form->errors = arr::overwrite($form->errors, $post->errors());
        Kohana::log("debug", print_r($form->errors,1));
      }
    }

    print $form;
  }
}