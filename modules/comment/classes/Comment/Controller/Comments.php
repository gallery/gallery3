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
class Comment_Controller_Comments extends Controller {
  /**
   * Add a new comment.  This generates the form, validates it, adds the comment,
   * and returns an ajaxified response.
   */
  public function action_add() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    if (!Comment::can_comment()) {
      throw HTTP_Exception::factory(403);
    }
    $author = Identity::active_user();

    // Build the comment model.
    $comment = ORM::factory("Comment");
    $comment->author_id = $author->id;
    $comment->item_id = $item->id;
    $comment->state = "published";  // The module events can alter this default if desired.

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-comment-form")
      ->add("comment", "group")
      ->add("other", "group");
    $form->comment
      ->set("label", t("Add comment"))
      ->add("guest_name", "input")
      ->add("guest_email", "input")
      ->add("guest_url", "input")
      ->add("text", "textarea");
    $form->comment->guest_name
      ->attr("id", "g-author")
      ->set("label", t("Name"))
      ->set("error_messages", array("not_empty" => t("You must enter a name for yourself")));
    $form->comment->guest_email
      ->attr("id", "g-email")
      ->set("label", t("Email (hidden)"))
      ->set("error_messages", array("not_empty" => t("You must enter a valid email address"),
                                    "email"     => t("You must enter a valid email address")));
    $form->comment->guest_url
      ->attr("id", "g-url")
      ->set("label", t("Website (hidden)"))
      ->set("error_messages", array("url"       => t("You must enter a valid url")));
    $form->comment->text
      ->attr("id", "g-text")
      ->set("label", t("Comment"))
      ->set("error_messages", array("not_empty" => t("You must enter a comment")));
    $form->other
      ->set("label", "")
      ->add("submit", "input|submit", t("Add"));
    $form->other->submit
      ->add_class("ui-state-default ui-corner-all");  // @todo: do this in js, and add "Cancel".

    // Link the ORM model and call the form events.
    $form->comment->orm("link", array("model" => $comment));
    Module::event("comment_add_form", $form);
    Module::event("captcha_protect_form", $form);

    // If we have a registered user, fill in their name, email, and url, and disable the fields.
    if (!$author->guest) {
      $form->comment->guest_name
        ->attr("disabled", "disabled")
        ->val($author->full_name);
      $form->comment->guest_email
        ->attr("disabled", "disabled")
        ->val($author->email);
      $form->comment->guest_url
        ->attr("disabled", "disabled")
        ->val($author->url);
    }

    // Load and validate the form.
    if ($form->load()->validate()) {
      $comment->save();

      $view = new View_Theme("comment/comment.html", "other", "comment-fragment");
      $view->comment = $comment;

      $form->set("response", array("html" => (string)$view));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "comment");

    $this->response->ajax_form($form);
  }
}
