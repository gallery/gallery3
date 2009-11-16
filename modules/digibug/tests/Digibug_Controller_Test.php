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
class Digibug_Controller_Test extends Unit_Test_Case {
  private $_proxy;
  private $_item;
  private $_server;

  public function teardown() {
    $_SERVER = $this->_server;

    if ($this->_proxy) {
      $this->_proxy->delete();
    }
  }

  public function setup() {
    $this->_server = $_SERVER;

    $root = ORM::factory("item", 1);
    $this->_album = album::create($root,  rand(), "test album");
    access::deny(identity::everybody(), "view_full", $this->_album);
    access::deny(identity::registered_users(), "view_full", $this->_album);

    $rand = rand();
    $this->_item = photo::create($this->_album, MODPATH . "gallery/tests/test.jpg", "$rand.jpg",
                                 $rand, $rand);
    $this->_proxy = ORM::factory("digibug_proxy");
    $this->_proxy->uuid = md5(rand());
    $this->_proxy->item_id = $this->_item->id;
    $this->_proxy->save();
  }

  public function digibug_request_thumb_test() {
    $controller = new Digibug_Controller();
    $controller->print_proxy("thumb", $this->_proxy->uuid);
  }

  public function digibug_request_full_malicious_ip_test() {
    $_SERVER["REMOTE_ADDR"] = "123.123.123.123";
    try {
      $controller = new Digibug_Controller();
      $controller->print_proxy("full", $this->_proxy->uuid);
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
    $controller->print_proxy("full", $this->_proxy->uuid);
  }
}
