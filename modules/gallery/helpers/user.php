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

/**
 * This is the API for handling users.
 *
 * Note: by design, this class does not do any permission checking.
 */
class user_Core {
  static function get_edit_form($user) {
    $writable = self::is_writable();
    $form = new Forge("users/update/$user->id", "", "post", array("id" => "g-edit-user-form"));
    $form->set_attr("class", "g-narrow");
    $group = $form->group("edit_user")->label(t("Edit User: %name", array("name" => $user->name)));
    $group->input("full_name")->label(t("Full Name"))->id("g-fullname")->value($user->full_name);
    self::_add_locale_dropdown($group, $user);
    if ($writable) {
      $group->password("password")->label(t("Password"))->id("g-password");
      $group->password("password2")->label(t("Confirm Password"))->id("g-password2")
        ->matches($group->password);
    }
    $group->input("email")->label(t("Email"))->id("g-email")->value($user->email);
    $group->input("url")->label(t("URL"))->id("g-url")->value($user->url);
    $form->add_rules_from(self::get_edit_rules());

    module::event("user_edit_form", $user, $form);
    $group->submit("")->value(t("Save"));

    if (!$writable) {
      foreach ($group->inputs as $input) {
        $input->disabled("disabled");
      }
    }
    return $form;
  }

  static function get_edit_form_admin($user) {
    $writable = self::is_writable();
    $form = new Forge(
      "admin/users/edit_user/$user->id", "", "post", array("id" => "g-edit-user-form"));
    $group = $form->group("edit_user")->label(t("Edit User"));
    $group->input("name")->label(t("Username"))->id("g-username")->value($user->name);
    $group->inputs["name"]->error_messages(
      "in_use", t("There is already a user with that username"));
    $group->input("full_name")->label(t("Full Name"))->id("g-fullname")->value($user->full_name);
    self::_add_locale_dropdown($group, $user);
    if ($writable) {
      $group->password("password")->label(t("Password"))->id("g-password");
      $group->password("password2")->label(t("Confirm Password"))->id("g-password2")
        ->matches($group->password);
    }
    $group->input("email")->label(t("Email"))->id("g-email")->value($user->email);
    $group->input("url")->label(t("URL"))->id("g-url")->value($user->url);
    $group->checkbox("admin")->label(t("Admin"))->id("g-admin")->checked($user->admin);
    $form->add_rules_from(self::get_edit_rules());

    module::event("user_edit_form_admin", $user, $form);
    $group->submit("")->value(t("Modify User"));
    if (!$writable) {
      foreach ($group->inputs as $input) {
        $input->disabled("disabled");
      }
    }
    return $form;
  }

  static function get_add_form_admin() {
    $form = new Forge("admin/users/add_user", "", "post", array("id" => "g-add-user-form"));
    $form->set_attr('class', "g-narrow");
    $group = $form->group("add_user")->label(t("Add User"));
    $group->input("name")->label(t("Username"))->id("g-username")
      ->error_messages("in_use", t("There is already a user with that username"));
    $group->input("full_name")->label(t("Full Name"))->id("g-fullname");
    $group->password("password")->label(t("Password"))->id("g-password");
    $group->password("password2")->label(t("Confirm Password"))->id("g-password2")
      ->matches($group->password);
    $group->input("email")->label(t("Email"))->id("g-email");
    $group->input("url")->label(t("URL"))->id("g-url");
    self::_add_locale_dropdown($group);
    $group->checkbox("admin")->label(t("Admin"))->id("g-admin");
    $form->add_rules_from(self::get_edit_rules());

    module::event("user_add_form_admin", $user, $form);
    $group->submit("")->value(t("Add User"));
    return $form;
  }

  private static function _add_locale_dropdown(&$form, $user=null) {
    $locales = locales::installed();
    foreach ($locales as $locale => $display_name) {
      $locales[$locale] = SafeString::of_safe_html($display_name);
    }
    if (count($locales) > 1) {
      // Put "none" at the first position in the array
      $locales = array_merge(array("" => t("« none »")), $locales);
      $selected_locale = ($user && $user->locale) ? $user->locale : "";
      $form->dropdown("locale")
        ->label(t("Language Preference"))
        ->options($locales)
        ->selected($selected_locale);
    }
  }

  static function get_delete_form_admin($user) {
    $form = new Forge("admin/users/delete_user/$user->id", "", "post",
                      array("id" => "g-delete-user-form"));
    $group = $form->group("delete_user")->label(
      t("Are you sure you want to delete user %name?", array("name" => $user->name)));
    $group->submit("")->value(t("Delete user %name", array("name" => $user->name)));
    return $form;
  }

  static function get_login_form($url) {
    $form = new Forge($url, "", "post", array("id" => "g-login-form"));
    $form->set_attr('class', "g-narrow");
    $group = $form->group("login")->label(t("Login"));
    $group->input("name")->label(t("Username"))->id("g-username")->class(null);
    $group->password("password")->label(t("Password"))->id("g-password")->class(null);
    $group->inputs["name"]->error_messages("invalid_login", t("Invalid name or password"));
    $group->submit("")->value(t("Login"));
    return $form;
  }

  /**
   * Return the active user.  If there's no active user, return the guest user.
   *
   * @return User_Model
   */
  static function active() {
    // @todo (maybe) cache this object so we're not always doing session lookups.
    $user = Session::instance()->get("user", null);
    if (!isset($user)) {
      // Don't do this as a fallback in the Session::get() call because it can trigger unnecessary
      // work.
      $user = self::guest();
    }
    return $user;
  }

  /**
   * Change the active user.
   *
   * @return User_Model
   */
  static function set_active($user) {
    $session = Session::instance();
    $session->set("user", $user);
    $session->delete("group_ids");
    self::load_user();
  }

  /**
   * Return the array of group ids this user belongs to
   *
   * @return array
   */
  static function group_ids() {
    return Session::instance()->get("group_ids", array(1));
  }

  /**
   * Make sure that we have a session and group_ids cached in the session.  This is one
   * of the first calls to reference the user so call the Identity::instance to load the
   * driver classes.
   */
  static function load_user() {
    Identity::instance();
    $session = Session::instance();
    if (!($user = $session->get("user"))) {
      $session->set("user", $user = self::guest());
    }

    // The installer cannot set a user into the session, so it just sets an id which we should
    // upconvert into a user.
    // @todo what is user id===2
    if ($user === 2) {
      $user = model_cache::get("user", 2);
      self::login($user);
      $session->set("user", $user);
    }

    if (!$session->get("group_ids")) {
      $ids = array();
      foreach ($user->groups as $group) {
        $ids[] = $group->id;
      }
      $session->set("group_ids", $ids);
    }
  }

  /**
   * Log in as a given user.
   * @param object $user the user object.
   */
  static function login($user) {
    // @todo make this an interface call
    $user->login_count += 1;
    $user->last_login = time();
    $user->save();

    self::set_active($user);
    module::event("user_login", $user);
  }

  /**
   * Log out the active user and destroy the session.
   * @param object $user the user object.
   */
  static function logout() {
    $user = self::active();
    if (!$user->guest) {
      try {
        Session::instance()->destroy();
      } catch (Exception $e) {
        Kohana::log("error", $e);
      }
      module::event("user_logout", $user);
    }
  }

  /**
   * Determine if a feature is supported by the driver.
   *
   * @param  string  $feature the name of the feature to check
   * @return boolean true if supported
   */
  static function is_writable() {
    return Identity::instance()->is_writable();
  }

  /**
   * Return the guest user.
   *
   * @todo consider caching
   *
   * @return User_Model
   */
  static function guest() {
    return Identity::instance()->guest();
  }

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @return User_Model
   */
  static function create($name, $full_name, $password) {
    return Identity::instance()->create_user($name, $full_name, $password);
  }

  /**
   * Is the password provided correct?
   *
   * @param user User Model
   * @param string $password a plaintext password
   * @return boolean true if the password is correct
   */
  static function is_correct_password($user, $password) {
    return Identity::instance()->is_correct_password($user, $password);
  }

  /**
   * Create the hashed passwords.
   * @param string $password a plaintext password
   * @return string hashed password
   */
  static function hash_password($password) {
    return Identity::instance()->hash_password($password);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Model  the user object, or null if the id was invalid.
   */
  static function lookup($id) {
    return Identity::instance()->lookup_user_by_field("id", $id);
  }

  /**
   * Look up a user by name.
   * @param integer      $name the user name
   * @return User_Model  the user object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return Identity::instance()->lookup_user_by_field("name", $name);
  }


  /**
   * Look up a user by hash.
   * @param string       $name the user name
   * @return User_Model  the user object, or null if the name was invalid.
   */
  static function lookup_by_hash($hash) {
    return Identity::instance()->lookup_user_by_field("hash", $hash);
  }

  /**
   * List the users
   * @param mixed      options to apply to the selection of the user(optional)
   * @return array     the group list.
   */
  static function get_user_list($filter=array()) {
    return Identity::instance()->get_user_list($filter);
  }

  /**
   * Return the edit rules associated with an user.
   *
   * @return stdClass containing the rules
   */
  static function get_edit_rules() {
    return Identity::instance()->get_edit_rules("user");
  }
}