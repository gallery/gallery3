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
  function item() {
    return ORM::factory("Item", $this->item_id);
  }

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
  public function validate(Validation $array=null) {
    // validate() is recursive, only modify the rules on the outermost call.
    if (!$array) {
      $this->rules = array(
        "guest_name"  => array("callbacks" => array(array($this, "valid_author"))),
        "guest_email" => array("callbacks" => array(array($this, "valid_email"))),
        "guest_url"   => array("rules"     => array("url")),
        "item_id"     => array("callbacks" => array(array($this, "valid_item"))),
        "state"       => array("rules"     => array("Model_Comment::valid_state")),
        "text"        => array("rules"     => array("required")),
      );
    }

    parent::validate($array);
  }

  /**
   * @see ORM::save()
   */
  public function save() {
    $this->updated = time();
    if (!$this->loaded()) {
      // New comment
      $this->created = $this->updated;
      if (empty($this->state)) {
        $this->state = "published";
      }

      // These values are useful for spam fighting, so save them with the comment.  It's painful to
      // check each one to see if it already exists before setting it, so just use server_http_host
      // as a semaphore for now (we use that in g2_import.php)
      if (empty($this->server_http_host)) {
        $this->server_http_accept = substr($_SERVER["HTTP_ACCEPT"], 0, 128);
        $this->server_http_accept_charset = substr($_SERVER["HTTP_ACCEPT_CHARSET"], 0, 64);
        $this->server_http_accept_encoding = substr($_SERVER["HTTP_ACCEPT_ENCODING"], 0, 64);
        $this->server_http_accept_language = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 64);
        $this->server_http_connection = substr($_SERVER["HTTP_CONNECTION"], 0, 64);
        $this->server_http_host = substr($_SERVER["HTTP_HOST"], 0, 64);
        $this->server_http_referer = substr($_SERVER["HTTP_REFERER"], 0, 255);
        $this->server_http_user_agent = substr($_SERVER["HTTP_USER_AGENT"], 0, 128);
        $this->server_query_string = substr($_SERVER["QUERY_STRING"], 0, 64);
        $this->server_remote_addr = substr($_SERVER["REMOTE_ADDR"], 0, 40);
        $this->server_remote_host = substr($_SERVER["REMOTE_HOST"], 0, 255);
        $this->server_remote_port = substr($_SERVER["REMOTE_PORT"], 0, 16);
      }

      $visible_change = $this->state == "published";
      parent::save();
      Module::event("comment_created", $this);
    } else {
      // Updated comment
      $original = ORM::factory("Comment", $this->id);
      $visible_change = $original->state == "published" || $this->state == "published";
      parent::save();
      Module::event("comment_updated", $original, $this);
    }

    // We only notify on the related items if we're making a visible change.
    if ($visible_change) {
      $item = $this->item();
      Module::event("item_related_update", $item);
    }

    return $this;
  }

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  public function viewable() {
    $this->join("items", "items.id", "comments.item_id");
    return Item::viewable($this);
  }

  /**
   * Make sure we have an appropriate author id set, or a guest name.
   */
  public function valid_author(Validation $v, $field) {
    if (empty($this->author_id)) {
      $v->add_error("author_id", "required");
    } else if ($this->author_id == Identity::guest()->id && empty($this->guest_name)) {
      $v->add_error("guest_name", "required");
    }
  }

  /**
   * Make sure that the email address is legal.
   */
  public function valid_email(Validation $v, $field) {
    if ($this->author_id == Identity::guest()->id) {
      if (empty($v->guest_email)) {
        $v->add_error("guest_email", "required");
      } else if (!Valid::email($v->guest_email)) {
        $v->add_error("guest_email", "invalid");
      }
    }
  }

  /**
   * Make sure we have a valid associated item id.
   */
  public function valid_item(Validation $v, $field) {
    if (DB::build()
        ->from("items")
        ->where("id", "=", $this->item_id)
        ->count_records() != 1) {
      $v->add_error("item_id", "invalid");
    }
  }

  /**
   * Make sure that the state is legal.
   */
  static function valid_state($value) {
    return in_array($value, array("published", "unpublished", "spam", "deleted"));
  }

  /**
   * Same as ORM::as_array() but convert id fields into their RESTful form.
   */
  public function as_restful_array() {
    $data = array();
    foreach ($this->as_array() as $key => $value) {
      if (strncmp($key, "server_", 7)) {
        $data[$key] = $value;
      }
    }
    $data["item"] = Rest::url("item", $this->item());
    unset($data["item_id"]);

    return $data;
  }
}
