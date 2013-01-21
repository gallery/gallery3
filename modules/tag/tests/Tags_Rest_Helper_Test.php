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
class Tags_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    try {
      Database::instance()->query("TRUNCATE {tags}");
      Database::instance()->query("TRUNCATE {items_tags}");
    } catch (Exception $e) {
    }
  }

  public function teardown() {
    identity::set_active_user(identity::admin_user());
  }

  public function get_test() {
    $t1 = tag::add(item::root(), "t1");
    $t2 = tag::add(item::root(), "t2");

    $request = new stdClass();
    $this->assert_equal_array(
      array(
        "url" => rest::url("tags"),
        "members" => array(
          rest::url("tag", $t1),
          rest::url("tag", $t2))),
      tags_rest::get($request));
  }

  public function post_test() {
    identity::set_active_user(identity::admin_user());

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->name = "test tag";
    $this->assert_equal(
      array("url" => url::site("rest/tag/1")),
      tags_rest::post($request));
  }

  public function post_fails_without_permissions_test() {
    // We have to remove edit permissions from everywhere
    Database::instance()->query("UPDATE {access_caches} SET edit_1=0");
    identity::set_active_user(identity::guest());

    try {
      $request = new stdClass();
      $request->params = new stdClass();
      $request->params->entity = new stdClass();
      $request->params->entity->name = "test tag";
      tags_rest::post($request);
    } catch (Exception $e) {
      $this->assert_equal(403, $e->getCode());
      return;
    }
    $this->assert_true(false, "Shouldnt get here");
  }

}
