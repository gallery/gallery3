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
class Admin_Comments_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->page_title = t("Comment settings");
    $view->content = new View("admin_comments.html");
    $view->content->form = $this->_get_admin_form();
    print $view;
  }

  public function save() {
    access::verify_csrf();
    $form = $this->_get_admin_form();
    $form->validate();
    module::set_var("comment", "access_permissions",
                    $form->comment_settings->access_permissions->value);
    module::set_var("comment", "rss_visible",
                    $form->comment_settings->rss_visible->value);
    message::success(t("Comment settings updated"));
    url::redirect("admin/comments");
  }

  private function _get_admin_form() {
    $form = new Forge("admin/comments/save", "", "post",
                      array("id" => "g-comments-admin-form"));
    $comment_settings = $form->group("comment_settings")->label(t("Permissions"));
    $comment_settings->dropdown("access_permissions")
      ->label(t("Who can leave comments?"))
      ->options(array("everybody" => t("Everybody"),
                      "registered_users" => t("Only registered users")))
      ->selected(module::get_var("comment", "access_permissions"));
    $comment_settings->dropdown("rss_visible")
      ->label(t("Which RSS feeds can users see?"))
      ->options(array("all" => t("All comment feeds"),
                      "newest" => t("New comments feed only"),
                      "per_item" => t("Comments on photos, movies and albums only")))
      ->selected(module::get_var("comment", "rss_visible"));
    $comment_settings->submit("save")->value(t("Save"));
    return $form;
  }
}

