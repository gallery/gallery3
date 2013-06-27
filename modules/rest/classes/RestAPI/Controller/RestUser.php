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
class RestAPI_Controller_RestUser extends Controller {
  /**
   * Reset the REST API key.  This generates the form, validates it, resets the key,
   * and returns a response.  This is an ajax dialog from the user_profile view.
   */
  public function action_reset_access_key() {
    if (Identity::active_user()->guest) {
      throw HTTP_Exception::factory(403);
    }

    $form = Formo::form()
      ->attr("id", "g-reset-access-key")
      ->add("confirm", "group");
    $form->confirm
      ->set("label", t("Confirm resetting your REST API key"))
      ->html(t("Do you really want to reset your REST API key?  Any clients that use this key will need to be updated with the new value."))
      ->add("submit", "input|submit", t("Reset"));

    if ($form->load()->validate()) {
      RestAPI::reset_access_key();
      Message::success(t("Your REST API key has been reset."));
    }

    $this->response->ajax_form($form);
  }
}
