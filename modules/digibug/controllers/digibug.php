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
    access::required("view", $item);

    if (access::group_can(group::everybody(), "view_full", $item)) {
      $full_url = $item->file_url(true);
      $thumb_url = $item->thumb_url(true);
    } else {
      $proxy = ORM::factory("digibug_proxy");
      $proxy->uuid =  md5(rand());
      $proxy->item_id = $item->id;
      $proxy->save();
      $full_url = url::abs_site("digibug/print_proxy/full/$proxy->uuid");
      $thumb_url = url::abs_site("digibug/print_proxy/thumb/$proxy->uuid");
    }

    $v = new View("digibug_form.html");
    $v->order_parms = array(
      "digibug_api_version" => "100",
      "company_id" => module::get_var("digibug", "company_id"),
      "event_id" => module::get_var("digibug", "event_id"),
      "cmd" => "addimg",
      "partner_code" => "69",
      "return_url" => url::abs_site("digibug/close_window"),
      "num_images" => "1",
      "image_1" => $full_url,
      "thumb_1" => $thumb_url,
      "image_height_1" => $item->height,
      "image_width_1" => $item->width,
      "thumb_height_1" => $item->thumb_height,
      "thumb_width_1" => $item->thumb_width,
      "title_1" => html::purify($item->title));

    print $v;
  }

  public function print_proxy($type, $id) {
    // If its a request for the full size then make sure we are coming from an
    // authorized address
    if ($type == "full") {
      $remote_addr = ip2long($this->input->server("REMOTE_ADDR"));
      if ($remote_addr === false) {
        Kohana::show_404();
      }
      $config = Kohana::config("digibug");

      $authorized = false;
      foreach ($config["ranges"] as $ip_range) {
        $low = ip2long($ip_range["low"]);
        $high = ip2long($ip_range["high"]);
        $authorized = $low !== false && $high !== false &&
          $low <= $remote_addr && $remote_addr <= $high;
        if ($authorized) {
          break;
        }
      }
      if (!$authorized) {
        Kohana::show_404();
      }
    }

    $proxy = ORM::factory("digibug_proxy", array("uuid" => $id));
    if (!$proxy->loaded || !$proxy->item->loaded) {
      Kohana::show_404();
    }

    $file = $type == "full" ? $proxy->item->file_path() : $proxy->item->thumb_path();
    if (!file_exists($file)) {
      kohana::show_404();
    }

    // We don't need to save the session for this request
    Session::abort_save();

    if (!TEST_MODE) {
      // Dump out the image
      header("Content-Type: $proxy->item->mime_type");
      Kohana::close_buffers(false);
      $fd = fopen($file, "rb");
      fpassthru($fd);
      fclose($fd);

      // If the request was for the image and not the thumb, then delete the proxy.
      if ($type == "full") {
        $proxy->delete();
      }
    }

    $this->_clean_expired();
  }

  public function close_window() {
    print "<script type=\"text/javascript\">window.close();</script>";
  }

  private function _clean_expired() {
    Database::instance()->query(
      "DELETE FROM {digibug_proxies} " .
      "WHERE request_date <= (CURDATE() - INTERVAL 10 DAY) " .
      "LIMIT 20");
  }
}