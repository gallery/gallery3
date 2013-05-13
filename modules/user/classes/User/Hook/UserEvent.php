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
class User_Hook_UserEvent {
  /**
   * Setup the relationship between Model_User and Model_Group.
   */
  static function model_relationships($relationships) {
    $relationships["user"]["has_many"]["groups"] =
      array("through" => "groups_users", "delete_through" => true);
    $relationships["group"]["has_many"]["users"] =
      array("through" => "groups_users", "delete_through" => true);
  }

  static function admin_menu($menu, $theme) {
    $menu->add_after("appearance_menu", Menu::factory("link")
                     ->id("users_groups")
                     ->label(t("Users/Groups"))
                     ->url(URL::site("admin/users")));

    return $menu;
  }

  static function user_login_form($form) {
    // Add "Forgot your password?" link to login form
    if (Identity::is_writable() && !Module::get_var("gallery", "maintenance_mode")) {
      $form
        ->add_after_submit("forgot_password", "input")
        ->add_script_text(
          // Setting the focus when ready doesn't always work with IE7, perhaps because the field is
          // not ready yet?  So set a timeout and do it the next time we're idle.
            '$("#g-reset-password-form").ready(function() {
              setTimeout(\'$("#g-username").focus()\', 100);
            });'
          );
      $form->find("forgot_password")
        ->set("editable", false)
        ->val(HTML::anchor("password/reset", t("Forgot your password?"),
              array("id" => "g-reset-password", "class" => "g-dialog-link g-right g-text-small")));
    }
  }
}
