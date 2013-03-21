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

class user_profile_Core {
  /**
   * Generate the url to display the profile
   * @return url for the profile display
   */
  static function url($user_id) {
    return url::site("user_profile/show/{$user_id}");
  }

  static function get_contact_form($user) {
    $form = new Forge("user_profile/send/{$user->id}", "", "post",
                      array("id" => "g-user-profile-contact-form"));
    $group = $form->group("message")
      ->label(t("Compose message to %name", array("name" => $user->display_name())));
    $group->input("reply_to")
      ->label(t("From:"))
      ->rules("required|length[1, 256]|valid_email")
      ->error_messages("required", t("You must enter a valid email address"))
      ->error_messages("max_length", t("Your email address is too long"))
      ->error_messages("valid_email", t("You must enter a valid email address"));
    $group->input("subject")
      ->label(t("Subject:"))
      ->rules("required|length[1, 256]")
      ->error_messages("required", t("Your message must have a subject"))
      ->error_messages("max_length", t("Your subject is too long"));
    $group->textarea("message")
      ->label(t("Message:"))
      ->rules("required")
      ->error_messages("required", t("You must enter a message"));
    module::event("user_profile_contact_form", $form);
    module::event("captcha_protect_form", $form);
    $group->submit("")->value(t("Send"));
    return $form;
  }
}
