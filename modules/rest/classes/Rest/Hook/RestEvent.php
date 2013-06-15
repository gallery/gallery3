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
class Rest_Hook_RestEvent {
  /**
   * Called just before a user is deleted. This will remove the user from
   * the user_homes directory.
   */
  static function user_before_delete($user) {
    DB::delete("user_access_keys")
       ->where("id", "=", $user->id)
       ->execute();
  }


  static function change_provider($new_provider) {
    DB::delete("user_access_keys")
      ->execute();
  }

  /**
   * Called after a user has been added.  Just add a remote access key
   * on every add.
   */
  static function user_add_form_admin_completed($user, $form) {
    Rest::access_key($user);
  }

  /**
   * Called when admin is editing a user
   */
  static function user_edit_form_admin($user, $form) {
    $form->add_before_submit("user_access_key", "input", Rest::access_key($user));
    $form->find("user_access_key")
      ->set("label", t("Remote access key"))
      ->attr("disabled", "disabled");
  }

  static function show_user_profile($data) {
    // Guests can't see a REST key
    if (Identity::active_user()->guest) {
      return;
    }

    // Only logged in users can see their own REST key
    if (Identity::active_user()->id != $data->user->id) {
      return;
    }

    $view = new View("rest/user_profile.html");
    $view->rest_key = Rest::access_key();

    $data->content[] = (object)array("title" => t("REST API"), "view" => $view);
  }

  static function admin_menu($menu, $theme) {
    $menu->get("settings_menu")
      ->append(Menu::factory("link")
               ->id("rest")
               ->label(t("REST API"))
               ->url(URL::site("admin/rest")));
  }
}
