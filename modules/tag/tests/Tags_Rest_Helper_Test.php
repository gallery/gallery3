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
class Tags_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    try {
      Database::instance()->query("TRUNCATE {tags}");
      Database::instance()->query("TRUNCATE {items_tags}");
    } catch (Exception $e) { }
    $this->_save = array($_GET, $_POST, $_SERVER);
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;
  }

  public function get_test() {
    tag::add(item::root(), "t1");
    tag::add(item::root(), "t2");

    $request = new stdClass();
    $this->assert_equal_array(
      array(
        "members" => array(
          "t1" => rest::url("tag", ORM::factory("tag")->where("name", "=", "t1")->find()),
          "t2" => rest::url("tag", ORM::factory("tag")->where("name", "=", "t2")->find()))),
      tags_rest::get($request));
  }

  public function post_test() {
    access::allow(identity::everybody(), "edit", item::root());

    $request->params->name = "test tag";
    $this->assert_equal(
      array("url" => url::site("rest/tag/test%20tag")),
      tags_rest::post($request));
  }

  public function post_fails_without_permissions_test() {
    access::deny(identity::everybody(), "edit", item::root());

    try {
      $request->params->name = "test tag";
      tags_rest::post($request);
    } catch (Exception $e) {
      $this->assert_equal(403, $e->getCode());
      return;
    }
    $this->assert_true(false, "Shouldnt get here");
  }

}
