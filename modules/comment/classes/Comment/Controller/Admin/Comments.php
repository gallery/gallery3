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
class Comment_Controller_Admin_Comments extends Controller_Admin {
  public function action_index() {
    $form = Formo::form()
      ->add("comment", "group");
    $form->comment
      ->add("access_permissions", "select", Module::get_var("comment", "access_permissions"))
      ->add("rss_visible",        "select", Module::get_var("comment", "rss_visible"))
      ->add("submit",             "input|submit", t("Save"));

    $form
      ->attr("id", "g-comments-admin-form");
    $form->comment
      ->set("label", t("Permissions"));
    $form->comment->access_permissions
      ->set("label", t("Who can leave comments?"))
      ->set("opts", array(
          "everybody"        => t("Everybody"),
          "registered_users" => t("Only registered users")
        ));
    $form->comment->rss_visible
      ->set("label", t("Which RSS feeds can users see?"))
      ->set("opts", array(
          "all"      => t("All comment feeds"),
          "newest"   => t("New comments feed only"),
          "per_item" => t("Comments on photos, movies and albums only")
        ));

    if ($form->load()->validate()) {
      Module::set_var("comment", "access_permissions", $form->comment->access_permissions->val());
      Module::set_var("comment", "rss_visible",        $form->comment->rss_visible->val());
      Message::success(t("Comment settings updated"));
    }

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Comment settings");
    $view->content = new View("admin/comments.html");
    $view->content->form = $form;

    $this->response->body($view);
  }
}
