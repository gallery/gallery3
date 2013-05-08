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
class Gallery_Controller_UserProfile extends Controller {
  public function action_show() {
    $id = $this->request->arg(0, "digit");
    // If we get here, then we should have a user id other than guest.
    $user = Identity::lookup_user($id);
    if (!$user) {
      throw HTTP_Exception::factory(404);
    }

    if (!$this->_can_view_profile_pages($user)) {
      throw HTTP_Exception::factory(404);
    }

    $v = new View_Theme("required/page.html", "other", "profile");
    $v->page_title = t("%name Profile", array("name" => $user->display_name()));
    $v->content = new View("gallery/user_profile.html");

    $v->content->user = $user;
    $v->content->contactable =
      !$user->guest && $user->id != Identity::active_user()->id && $user->email;
    $v->content->editable =
      Identity::is_writable() && !$user->guest && $user->id == Identity::active_user()->id;

    $event_data = (object)array("user" => $user, "content" => array());
    Module::event("show_user_profile", $event_data);
    $v->content->info_parts = $event_data->content;

    $this->response->body($v);
  }

  public function action_contact() {
    $id = $this->request->arg(0, "digit");
    $user = Identity::lookup_user($id);
    if (!$this->_can_view_profile_pages($user)) {
      throw HTTP_Exception::factory(404);
    }

    $form = Formo::form()
      ->attr("id", "g-user-profile-contact-form")
      ->add("email", "group");
    $form->email
      ->set("label", t("Compose message to %name", array("name" => $user->display_name())))
      ->add("reply_to", "input", Identity::active_user()->email)
      ->add("subject", "input")
      ->add("message", "textarea")
      ->add("submit", "input|submit", t("Send"));
    $form->email->reply_to
      ->set("label", t("From:"))
      ->add_rule("not_empty",  array(":value"),      t("You must enter a valid email address"))
      ->add_rule("max_length", array(":value", 256), t("Your email address is too long"))
      ->add_rule("email",      array(":value"),      t("You must enter a valid email address"));
    $form->email->subject
      ->set("label", t("Subject:"))
      ->add_rule("not_empty",  array(":value"),      t("Your message must have a subject"))
      ->add_rule("max_length", array(":value", 256), t("Your subject is too long"));
    $form->email->message
      ->set("label", t("Message:"))
      ->add_rule("not_empty",  array(":value"),      t("You must enter a message"));

    Module::event("user_profile_contact_form", $form);
    //Module::event("captcha_protect_form", $form);  // @todo: make this work!

    if ($form->load()->validate()) {
      Sendmail::factory()
        ->to($user->email)
        ->subject(HTML::clean($form->email->subject->val()))
        ->header("Mime-Version", "1.0")
        ->header("Content-type", "text/html; charset=UTF-8")
        ->reply_to($form->email->reply_to->val())
        ->message(HTML::purify($form->email->message->val()))
        ->send();
      Message::success(t("Sent message to %user_name", array("user_name" => $user->display_name())));
    }

    $this->response->ajax_form($form);
  }

  protected function _can_view_profile_pages($user) {
    if (!$user->loaded()) {
      return false;
    }

    if ($user->id == Identity::active_user()->id) {
      // You can always view your own profile
      return true;
    }

    switch (Module::get_var("gallery", "show_user_profiles_to")) {
    case "admin_users":
      return Identity::active_user()->admin;

    case "registered_users":
      return !Identity::active_user()->guest;

    case "everybody":
      return true;

    default:
      // Fail in private mode on an invalid setting
      return false;
    }
  }
}
