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
class User_Model_User extends ORM implements IdentityProvider_UserDefinition {
  protected $password_length = null;

  public function set($column, $value) {
    switch ($column) {
    case "hashed_password":
      $column = "password";
      break;

    case "password":
      $this->password_length = strlen($value);
      $value = User::hash_password($value);
      break;
    }
    parent::set($column, $value);
  }

  /**
   * @see ORM::delete()
   */
  public function delete() {
    $old = clone $this;
    Module::event("user_before_delete", $this);
    parent::delete();
    Module::event("user_deleted", $old);

    return $this;
  }

  /**
   * Return a url to the user's avatar image.
   * @param integer $size the target size of the image (default 80px)
   * @return string a url
   */
  public function avatar_url($size=80, $default=null) {
    // If default is not set, use "assets/required/avatar.jpg"
    $default = $default ?: URL::abs_file(str_replace(DOCROOT, "",
      Kohana::find_file("assets/required", "avatar", "jpg")));

    return sprintf("http://www.gravatar.com/avatar/%s.jpg?s=%d&r=pg%s",
                   md5($this->email), $size, $default ? "&d=" . urlencode($default) : "");
  }

  public function groups() {
    return $this->groups->find_all()->as_array();
  }

  /**
   * Specify our validation rules.
   */
  public function rules() {
    $rules = array(
      "admin" => array(
        array(array($this, "valid_admin"), array(":validation"))
      ),
      "email" => array(
        array("max_length", array(":value", 255)),
        array("email")
      ),
      "full_name" => array(
        array("max_length", array(":value", 255))
      ),
      "locale" => array(
        array("min_length", array(":value", 2)),
        array("max_length", array(":value", 10))
      ),
      "name" => array(
        array("not_empty"),
        array("max_length", array(":value", 32)),
        array(array($this, "valid_name"), array(":validation"))
      ),
      "url" => array(
        array("url")
      )
    );

    // Registered users have additional rules for email and password
    if (!$this->guest) {
      $rules["email"][]    = array("not_empty");
      $rules["password"][] = array(array($this, "valid_password"), array(":validation"));
    }

    return $rules;
  }

  /**
   * Handle any business logic necessary to save (i.e. create or update) a user.
   * @see ORM::save()
   *
   * @return ORM Model_User
   */
  public function save(Validation $validation=null) {
    if ($this->full_name === null) {
      $this->full_name = "";
    }

    return parent::save($validation);
  }

  /**
   * Handle any business logic necessary to create a user.
   * @see ORM::create()
   *
   * @return ORM Model_User
   */
  public function create(Validation $validation=null) {
    Module::event("user_before_create");

    parent::create($validation);

    $this->add("groups", Group::everybody());
    if (!$this->guest) {
      $this->add("groups", Group::registered_users());
    }

    Module::event("user_created", $this);

    return $this;
  }

  /**
   * Handle any business logic necessary to update a user.
   * @see ORM::update()
   *
   * @return ORM Model_User
   */
  public function update(Validation $validation=null) {
    Module::event("user_before_update");
    $original = ORM::factory("User", $this->id);
    parent::update();
    Module::event("user_updated", $original, $this);

    return $this;
  }

  /**
   * Return the best version of the user's name.  Either their specified full name, or fall back
   * to the user name.
   * @return string
   */
  public function display_name() {
    return empty($this->full_name) ? $this->name : $this->full_name;
  }

  /**
   * Validate the user name.  Make sure there are no conflicts.
   */
  public function valid_name(Validation $v) {
    if (ORM::factory("User")
        ->where("name", "=", $this->name)
        ->where("id", "<>", $this->id)
        ->find()->loaded()) {
      $v->error("name", "conflict");
    }
  }

  /**
   * Validate the password.
   */
  public function valid_password(Validation $v) {
    if (!$this->loaded() || isset($this->password_length)) {
      $minimum_length = Module::get_var("user", "minimum_password_length", 5);
      if ($this->password_length < $minimum_length) {
        $v->error("password", "min_length");
      }
    }
  }

  /**
   * Validate the admin bit.
   */
  public function valid_admin(Validation $v) {
    $active = Identity::active_user();
    if ($this->id == $active->id && $active->admin && !$this->admin) {
      $v->error("admin", "locked");
    }
  }
}
