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
class Comments_Controller extends Controller {
  /**
   * Add a new comment to the collection.
   */
  public function create($id) {
    $item = ORM::factory("item", $id);
    access::required("view", $item);
    if (!comment::can_comment()) {
      access::forbidden();
    }

    $form = comment::get_add_form($item);
    try {
      $valid = $form->validate();
      $comment = ORM::factory("comment");
      $comment->item_id = $id;
      $comment->author_id = identity::active_user()->id;
      $comment->text = $form->add_comment->text->value;
      $comment->guest_name = $form->add_comment->inputs["name"]->value;
      $comment->guest_email = $form->add_comment->email->value;
      $comment->guest_url = $form->add_comment->url->value;
      $comment->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        switch ($key) {
        case "guest_name":  $key = "name";  break;
        case "guest_email": $key = "email"; break;
        case "guest_url":   $key = "url";   break;
        }
        $form->add_comment->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $comment->save();
      $view = new Theme_View("comment.html", "other", "comment-fragment");
      $view->comment = $comment;

      json::reply(array("result" => "success",
                        "view" => (string)$view,
                        "form" => (string)comment::get_add_form($item)));
    } else {
      $form = comment::prefill_add_form($form);
      json::reply(array("result" => "error", "form" => (string)$form));
    }
  }

  /**
   * Present a form for adding a new comment to this item or editing an existing comment.
   */
  public function form_add($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);
    if (!comment::can_comment()) {
      access::forbidden();
    }

    print comment::prefill_add_form(comment::get_add_form($item));
  }
}
