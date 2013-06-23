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
class Rest_Mock extends Rest {
  public $entity = array();
  public $members = array();

  public function __construct($id, $params) {
    if ($id) {
      // Object
      $this->entity = array(
        "id"  => $id,
        "foo" => "bar");
    } else {
      // Collection
      $this->members = array(
        Rest::factory("Mock", 1),
        Rest::factory("Mock", 2),
        Rest::factory("Mock", 3));
    }

    parent::__construct($id, $params);
  }

  public function get_entity() {
    return empty($this->entity) ? null : $this->entity;
  }

  public function put_entity() {
    if (property_exists($this->params["entity"], "exception")) {
      switch ($this->params["entity"]->exception) {
      case "orm":
        // Throw an ORM_Validation_Exception (will be "type" => array(0 => "read_only", 1 => null))
        $item = Test::random_album();
        $item->type = "cannot_change";
        $item->save();
        break;

      case "gallery":
        // Throw a Gallery_Exception
        throw new Gallery_Exception("mock exception");
        break;

      case "rest":
        // Throw a Rest_Exception
        throw Rest_Exception::factory(400, array("mock" => "exception"));
        break;

      case "http":
        // Throw an HTTP_Exception
        throw HTTP_Exception::factory(400, "mock exception");
        break;
      }
    }

    $this->entity = $this->params["entity"];
  }

  public function post_entity() {
    $this->entity = (array)$this->params["entity"];

    // If we had no id, it's new; if we did, it's existing.
    $this->created = empty($this->id);
    $this->id = $this->entity["id"];
  }

  public function get_members() {
    return empty($this->members) ? null : $this->members;
  }

  public function put_members() {
    // Replace members
    $this->members = $this->params["members"];
  }

  public function post_members() {
    // Add members only
    $this->members = array_merge($this->members, $this->params["members"]);
  }

  public function delete() {
    $this->id = null;
    $this->entity = array();
    $this->members = array();
  }
}
