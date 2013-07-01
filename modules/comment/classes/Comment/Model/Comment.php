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
class Comment_Model_Comment extends ORM {
  // Set the default sorting.
  protected $_sorting = array("created" => "DESC", "id" => "DESC");

  function author() {
    return Identity::lookup_user($this->author_id);
  }

  function author_name() {
    $author = $this->author();
    if ($author->guest) {
      return $this->guest_name;
    } else {
      return $author->display_name();
    }
  }

  function author_email() {
    $author = $this->author();
    if ($author->guest) {
      return $this->guest_email;
    } else {
      return $author->email;
    }
  }

  function author_url() {
    $author = $this->author();
    if ($author->guest) {
      return $this->guest_url;
    } else {
      return $author->url;
    }
  }

  /**
   * Add some custom per-instance rules.
   */
  public function rules() {
    $rules = array(
      "author_id" => array(array("not_empty")),
      "item_id"   => array(array(array($this, "valid_item"), array(":validation"))),
      "state"     => array(array("in_array", array(":value",
                       array("published", "unpublished", "spam", "deleted")))),
      "text"      => array(array("not_empty")),
    );

    // If the active user is a guest, add some extra rules.
    if ($this->author_id == Identity::guest()->id) {
      $rules["guest_name"]  = array(array("not_empty"));
      $rules["guest_email"] = array(array("not_empty"), array("email"));
      $rules["guest_url"]   = array(array("url"));
    }

    return $rules;
  }

  /**
   * Handle any business logic necessary to save (i.e. create or update) a comment.
   * @see ORM::save()
   */
  public function save(Validation $validation=null) {
    // If the author isn't a guest, blank the guest name, email, and url fields.
    if ($this->author_id != Identity::guest()->id) {
      $this->guest_name = null;
      $this->guest_email = null;
      $this->guest_url = null;
    }

    $this->updated = time();
    $original_state = Arr::get($this->original_values(), "state");

    parent::save($validation);

    // We only notify on the related items if we're making a visible change.
    if (($this->state == "published") || ($original_state == "published")) {
      $item = $this->item;
      Module::event("item_related_update", $item);
    }

    return $this;
  }

  /**
   * Handle any business logic necessary to create a comment.
   * @see ORM::create()
   */
  public function create(Validation $validation=null) {
    $this->created = $this->updated;
    Module::event("comment_before_create", $this);

    if (empty($this->state)) {
      $this->state = "published";
    }

    // These values are useful for spam fighting, so save them with the comment.  It's painful to
    // check each one to see if it already exists before setting it, so just use server_name
    // as a semaphore for now (we use that in G2Import.php)
    if (empty($this->server_name)) {
      $this->server_name =
        substr((isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] :
               (isset($_SERVER["HTTP_HOST"])   ? $_SERVER["HTTP_HOST"]   : "")), 0, 64);
      foreach (array("HTTP_ACCEPT" => 128,
                     "HTTP_ACCEPT_CHARSET" => 64,
                     "HTTP_ACCEPT_ENCODING" => 64,
                     "HTTP_ACCEPT_LANGUAGE" => 64,
                     "HTTP_CONNECTION" => 64,
                     "HTTP_REFERER" => 255,
                     "HTTP_USER_AGENT" => 128,
                     "QUERY_STRING" => 64,
                     "REMOTE_ADDR" => 40,
                     "REMOTE_HOST" => 255,
                     "REMOTE_PORT" => 16) as $var => $limit) {
        $this->{strtolower("server_$var")} =
          substr((isset($_SERVER[$var]) ? $_SERVER[$var] : ""), 0, $limit);
      }
    }

    parent::create($validation);
    Module::event("comment_created", $this);

    return $this;
  }

  /**
   * Handle any business logic necessary to update a comment.
   * @see ORM::update()
   */
  public function update(Validation $validation=null) {
    Module::event("comment_before_update", $this);
    $original = ORM::factory("Comment", $this->id);
    parent::update($validation);
    Module::event("comment_updated", $original, $this);

    return $this;
  }

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  public function viewable() {
    $this->with("item");
    return Item::viewable($this);
  }

  /**
   * Make sure we have a valid associated item id.
   */
  public function valid_item(Validation $v) {
    if (!$this->item->loaded()) {
      $v->error("item_id", "invalid");
    }
  }
}
