<?php defined("SYSPATH") or die("No direct script access.");/**
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
class rest_event {
  /**
   * Called just before a user is deleted. This will remove the user from
   * the user_homes directory.
   */
  static function user_before_delete($user) {
     ORM::factory("rest_key")
      ->where("id", $user->id)
      ->delete_all();
  }

  /**
   * Called after a user has been added.  Just add a remote access key
   * on every add.
   */
  static function user_add_form_admin_completed($user, $form) {
    $key = ORM::factory("rest_key");
    $key->user_id = $user->id;
    $key->access_key = md5($user->name . rand());
    $key->save();
  }

  /**
   * Called when admin is editing a user
   */
  static function user_edit_form_admin($user, $form) {
    self::_get_access_key_form($user, $form);
  }

  /**
   * Called when user is editing their own form
   */
  static function user_edit_form($user, $form) {
    self::_get_access_key_form($user, $form);
  }

  /**
   * Get the form fields for user edit
   */
  static function _get_access_key_form($user, $form) {
    $key = ORM::factory("rest_key")
      ->where("user_id", $user->id)
      ->find();

    if (!$key->loaded) {
      $key->user_id = $user->id;
      $key->access_key = md5($user->name . rand());
      $key->save();
    }

    $form->edit_user->input("access_key")
      ->value($key->access_key)
      ->readonly("readonly")
      ->class("g-form-static")
      ->label(t("Remote access key"));
  }
}
