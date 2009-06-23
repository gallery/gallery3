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
class Digibug_Controller extends Controller {
  public function print_photo($id) {
    access::verify_csrf();

    $item = ORM::factory("item", $id);

    $proxy = ORM::factory("proxy");
    $proxy->uuid = digibug::uuid();
    $proxy->item_id = $item->id;
    $proxy->save();

    $url = url::abs_site("digibug/print_proxy/{$proxy->uuid}");
    if (module::get_var("digibug", "mode", "basic")) {
      $company_id = module::get_var("digibug", "basic_company_id");
      $event_id = module::get_var("digibug", "basic_event_id");
    } else {
      $company_id = module::get_var("digibug", "company_id");
      $event_id = module::get_var("digibug", "event_id");
    }
    $digibug_parms = array(
      "digibug_api_version" => "100",
      "company_id" => $company_id,
      "event_id" => $event_id,
      "cmd" => "adding",
      "return_url" => url::abs_site($this->input->get("return")),
      "num_images" => "1",
      "image_1" => $url,
      "thumb_1" => "$url/thumb",
      "image_height_1" => $item->height,
      "image_width_1" => $item->width,
      "thumb_height_1" => $item->thumb_height,
      "thumb_width_1" => $item->thumb_width,
      "title" => $item->title);

    message::success(
      t("Photo '%title' was submitted for printing.", array("title" => $item->title)));
    print json_encode(array("result" => "success", "reload" => 1));
  }

  public function print_proxy($id, $thumb=null) {
    $proxy = ORM::factory("proxy")
      ->where("uuid", $id)
      ->find();

    if (!$proxy->loaded) {
      Kohana::show_404();
    }

    if (!$proxy->item->loaded) {
      Kohana::show_404();
    }

    $file = empty($thumb) ? $proxy->item->file_path() : $proxy->item->thumb_path();
    if (!file_exists($file)) {
      kohana::show_404();
    }

    // We don't need to save the session for this request
    Session::abort_save();

    // Dump out the image
    header("Content-Type: $proxy->item->mime_type");
    Kohana::close_buffers(false);
    $fd = fopen($file, "rb");
    fpassthru($fd);
    fclose($fd);

    // If the request was for the image and not the thumb, then delete the proxy.
    if (empty($thumb)) {
      $proxy->delete();
    }
  }

}