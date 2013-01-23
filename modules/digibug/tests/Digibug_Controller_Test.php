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
class Digibug_Controller_Test extends Gallery_Unit_Test_Case {
  private $_server;

  public function setup() {
    $this->_server = $_SERVER;
  }

  public function teardown() {
    $_SERVER = $this->_server;
  }

  private function _get_proxy() {
    $album = test::random_album();
    $photo = test::random_photo($album);

    access::deny(identity::everybody(), "view_full", $album);
    access::deny(identity::registered_users(), "view_full", $album);

    $proxy = ORM::factory("digibug_proxy");
    $proxy->uuid = random::hash();
    $proxy->item_id = $photo->id;
    return $proxy->save();
  }

  public function digibug_request_thumb_test() {
    $proxy = $this->_get_proxy();

    $controller = new Digibug_Controller();
    $controller->print_proxy("thumb", $proxy->uuid);
  }

  public function digibug_request_full_malicious_ip_test() {
    $_SERVER["REMOTE_ADDR"] = "123.123.123.123";
    try {
      $controller = new Digibug_Controller();
      $controller->print_proxy("full", $this->_get_proxy()->uuid);
      $this->assert_true(false, "Should have failed with an 404 exception");
    } catch (Kohana_404_Exception $e) {
      // expected behavior
    }
  }

  public function digibug_request_full_authorized_ip_test() {
    $config = Kohana::config("digibug");
    $this->assert_true(!empty($config), "The Digibug config is empty");

    $ranges = array_values($config["ranges"]);
    $low = ip2long($ranges[0]["low"]);
    $high = ip2long($ranges[0]["high"]);

    $_SERVER["REMOTE_ADDR"] = long2ip(rand($low, $high));
    $controller = new Digibug_Controller();
    $controller->print_proxy("full", $this->_get_proxy()->uuid);
  }
}
