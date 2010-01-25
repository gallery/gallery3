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
class User_Profile_Controller extends Controller {
  public function show($id) {
    // If we get here, then we should have a user id other than guest.
    $user = identity::lookup_user($id);
    $active_user = identity::active_user();
    $is_current_active = $active_user->id == $id;
    $display_all =  $active_user->admin || ($is_current_active && !$active_user->guest);

    $v = new Theme_View("page.html", "other", "profile");
    $v->page_title = t("%name Profile", array("name" => $user->display_name()));
    $v->content = new View("user_profile.html");

    // @todo modify user_home to supply a link to their album,
    $v->content->user = $user;
    $v->content->not_current = !$is_current_active;
    $v->content->editable = identity::is_writable() && $display_all;
    $v->content->return = SafeString::of(Input::instance()->get("return"));

    $event_data = (object)array("user" => $user, "display_all" => $display_all, "content" => array());
    module::event("show_user_profile", $event_data);
    $v->content->info_parts = $event_data->content;

    print $v;
  }

  public function contact($id) {
    $user = identity::lookup_user($id);
    print user_profile::get_contact_form($user);
  }

  public function send($id) {
    access::verify_csrf();
    $user = identity::lookup_user($id);
    $form = user_profile::get_contact_form($user);
    if ($form->validate()) {
      Sendmail::factory()
        ->to($user->email)
        ->subject($form->message->subject->value)
        ->header("Mime-Version", "1.0")
        ->header("Content-type", "text/html; charset=iso-8859-1")
        ->reply_to($form->message->reply_to->value)
        ->message($form->message->message->value)
        ->send();
      message::success(t("Sent message to %user_name", array("user_name" => $user->display_name())));
      print json_encode(array("result" => "success"));
    } else {
      print json_encode(array("result" => "error", "form" => (string)$form));
    }
  }
}
