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
class Comments_Controller extends Controller {
  /**
   * Add a new comment to the collection.
   */
  public function create($id) {
    $item = ORM::factory("item", $id);
    access::required("view", $item);

    $form = comment::get_add_form($item);
    $valid = $form->validate();
    if ($valid) {
      if (identity::active_user()->guest && !$form->add_comment->inputs["name"]->value) {
        $form->add_comment->inputs["name"]->add_error("missing", 1);
        $valid = false;
      }

      if (!$form->add_comment->text->value) {
        $form->add_comment->text->add_error("missing", 1);
        $valid = false;
      }
    }

    if ($valid) {
      $comment = comment::create(
        $item, identity::active_user(),
        $form->add_comment->text->value,
        $form->add_comment->inputs["name"]->value,
        $form->add_comment->email->value,
        $form->add_comment->url->value);

      $active = identity::active_user();
      if ($active->guest) {
        $form->add_comment->inputs["name"]->value("");
        $form->add_comment->email->value("");
        $form->add_comment->url->value("");
      } else {
        $form->add_comment->inputs["name"]->value($active->full_name);
        $form->add_comment->email->value($active->email);
        $form->add_comment->url->value($active->url);
      }

      $form->add_comment->text->value("");
      $view = new Theme_View("comment.html", "other", "comment-fragment");
      $view->comment = $comment;

      print json_encode(
        array("result" => "success",
              "view" => $view->__toString(),
              "form" => $form->__toString()));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  /**
   * Present a form for adding a new comment to this item or editing an existing comment.
   */
  public function form_add($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);

    print comment::get_add_form($item);
  }
}
