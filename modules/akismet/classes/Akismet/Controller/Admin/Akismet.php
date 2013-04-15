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
class Akismet_Controller_Admin_Akismet extends Controller_Admin {
  public function action_index() {
    $form = Akismet::get_configure_form();

    if (Request::current()->method() == HTTP_Request::POST) {
      // @todo move the "post" handler part of this code into a separate function
      Access::verify_csrf();

      if ($form->validate()) {
        $new_key = $form->configure_akismet->api_key->value;
        $old_key = Module::get_var("akismet", "api_key");
        if ($old_key && !$new_key) {
          Message::success(t("Your Akismet key has been cleared."));
        } else if ($old_key && $new_key && $old_key != $new_key) {
          Message::success(t("Your Akismet key has been changed."));
        } else if (!$old_key && $new_key) {
          Message::success(t("Your Akismet key has been saved."));
        }

        GalleryLog::success("akismet", t("Akismet key changed to %new_key",
                                  array("new_key" => $new_key)));
        Module::set_var("akismet", "api_key", $new_key);
        Akismet::check_config();
        HTTP::redirect("admin/akismet");
      } else {
        $valid_key = false;
      }
    } else {
      $valid_key = Module::get_var("akismet", "api_key") ? 1 : 0;
    }

    Akismet::check_config();
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Akismet spam filtering");
    $view->content = new View("admin/akismet.html");
    $view->content->valid_key = $valid_key;
    $view->content->form = $form;
    print $view;
  }

  public function action_stats() {
    $view = new View_Admin("required/admin.html");
    $view->content = new View("admin/akismet_stats.html");
    $view->content->api_key = Module::get_var("akismet", "api_key");
    $view->content->blog_url = URL::base("http", false);
    print $view;
  }
}