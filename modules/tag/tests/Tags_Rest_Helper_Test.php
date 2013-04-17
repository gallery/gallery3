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
class Tags_Rest_Helper_Test extends Unittest_Testcase {
  public function setup() {
    try {
      Database::instance()->query(Database::TRUNCATE, "TRUNCATE {tags}");
      Database::instance()->query(Database::TRUNCATE, "TRUNCATE {items_tags}");
    } catch (Exception $e) {
    }
  }

  public function teardown() {
    Identity::set_active_user(Identity::admin_user());
  }

  public function get_test() {
    $t1 = Tag::add(Item::root(), "t1");
    $t2 = Tag::add(Item::root(), "t2");

    $request = new stdClass();
    $this->assert_equal_array(
      array(
        "url" => Rest::url("tags"),
        "members" => array(
          Rest::url("tag", $t1),
          Rest::url("tag", $t2))),
      Hook_Rest_Tags::get($request));
  }

  public function post_test() {
    Identity::set_active_user(Identity::admin_user());

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->name = "test tag";
    $this->assert_equal(
      array("url" => URL::site("rest/tag/1")),
      Hook_Rest_Tags::post($request));
  }

  public function post_fails_without_permissions_test() {
    // We have to remove edit permissions from everywhere
    Database::instance()->query(Database::UPDATE, "UPDATE {access_caches} SET edit_1=0");
    Identity::set_active_user(Identity::guest());

    try {
      $request = new stdClass();
      $request->params = new stdClass();
      $request->params->entity = new stdClass();
      $request->params->entity->name = "test tag";
      Hook_Rest_Tags::post($request);
    } catch (Exception $e) {
      $this->assert_equal(403, $e->getCode());
      return;
    }
    $this->assert_true(false, "Shouldnt get here");
  }

}
