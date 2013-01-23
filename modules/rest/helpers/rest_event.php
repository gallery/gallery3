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
class rest_event {
  /**
   * Called just before a user is deleted. This will remove the user from
   * the user_homes directory.
   */
  static function user_before_delete($user) {
    db::build()
      ->delete("user_access_keys")
       ->where("id", "=", $user->id)
       ->execute();
  }


  static function change_provider($new_provider) {
    db::build()
      ->delete("user_access_keys")
      ->execute();
  }

  /**
   * Called after a user has been added.  Just add a remote access key
   * on every add.
   */
  static function user_add_form_admin_completed($user, $form) {
    $key = ORM::factory("user_access_key");
    $key->user_id = $user->id;
    $key->access_key = random::hash();
    $key->save();
  }

  /**
   * Called when admin is editing a user
   */
  static function user_edit_form_admin($user, $form) {
    self::_get_access_key_form($user, $form);
  }

  /**
   * Get the form fields for user edit
   */
  static function _get_access_key_form($user, $form) {
    $key = ORM::factory("user_access_key")
      ->where("user_id", "=", $user->id)
      ->find();

    if (!$key->loaded()) {
      $key->user_id = $user->id;
      $key->access_key = random::hash();
      $key->save();
    }

    $form->edit_user->input("user_access_key")
      ->value($key->access_key)
      ->readonly("readonly")
      ->class("g-form-static")
      ->label(t("Remote access key"));
  }

  static function show_user_profile($data) {
    // Guests can't see a REST key
    if (identity::active_user()->guest) {
      return;
    }

    // Only logged in users can see their own REST key
    if (identity::active_user()->id != $data->user->id) {
      return;
    }

    $view = new View("user_profile_rest.html");
    $key = ORM::factory("user_access_key")
      ->where("user_id", "=", $data->user->id)
      ->find();

    if (!$key->loaded()) {
      $key->user_id = $data->user->id;
      $key->access_key = random::hash();
      $key->save();
    }
    $view->rest_key = $key->access_key;
    $data->content[] = (object)array("title" => t("REST API"), "view" => $view);
  }
}
