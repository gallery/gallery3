<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Comments_Controller extends REST_Controller {
  protected $resource_type = "comment";

  /**
   * Display comments based on criteria.
   *  @see REST_Controller::_index()
   */
  public function _index() {
    $item = ORM::factory("item", $this->input->get('item_id'));
    access::required("view", $item);

    $comments = ORM::factory("comment")
      ->where("item_id", $item->id)
      ->where("state", "published")
      ->orderby("created", "desc")
      ->find_all();

    switch (rest::output_format()) {
    case "json":
      foreach ($comments as $comment) {
        $data[] = $comment->as_array();
      }
      print json_encode($data);
      break;

    case "html":
      $view = new View("comments.html");
      $view->comments = $comments;
      print $view;
      break;
    }
  }

  /**
   * Add a new comment to the collection.
   * @see REST_Controller::_create($resource)
   */
  public function _create($comment) {
    $item = ORM::factory("item", $this->input->post("item_id"));
    access::required("view", $item);

    $form = comment::get_add_form($item);
    $valid = $form->validate();
    if ($valid) {
      if (user::active()->guest && !$form->add_comment->inputs["name"]->value) {
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
        $item, user::active(),
        $form->add_comment->text->value,
        $form->add_comment->inputs["name"]->value,
        $form->add_comment->email->value,
        $form->add_comment->url->value);

      $form->add_comment->inputs["name"]->value("");
      $form->add_comment->text->value("");
      $form->add_comment->email->value("");
      $form->add_comment->url->value("");
      print json_encode(
        array("result" => "success",
              "resource" => ($comment->state == "published"
                             ? url::site("comments/{$comment->id}")
                             : null),
              "form" => $form->__toString()));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  /**
   * Display an existing comment.
   *  @todo Set proper Content-Type in a central place (REST_Controller::dispatch?).
   *  @see REST_Controller::_show($resource)
   */
  public function _show($comment) {
    $item = ORM::factory("item", $comment->item_id);
    access::required("view", $item);
    if ($comment->state != "published") {
      return;
    }

    if (rest::output_format() == "json") {
      print json_encode(
        array("result" => "success",
              "data" => $comment->as_array()));
    } else {
      $view = new Theme_View("comment.html", "fragment");
      $view->comment = $comment;
      print $view;
    }
  }

  /**
   * Change an existing comment.
   *  @see REST_Controller::_update($resource)
   */
  public function _update($comment) {
    $item = ORM::factory("item", $comment->item_id);
    access::required("edit", $item);

    $form = comment::get_edit_form($comment);
    if ($form->validate()) {
      $comment->guest_name = $form->edit_comment->inputs["name"]->value;
      $comment->guest_email = $form->edit_comment->email->value;
      $comment->url = $form->edit_comment->url->value;
      $comment->text = $form->edit_comment->text->value;
      $comment->save();
      module::event("comment_updated", $comment);

      print json_encode(
        array("result" => "success",
              "resource" => url::site("comments/{$comment->id}")));
    } else {
      print json_encode(
        array("result" => "error",
              "html" => $form->__toString()));
    }
  }

  /**
   * Delete existing comment.
   *  @see REST_Controller::_delete($resource)
   */
  public function _delete($comment) {
    $item = ORM::factory("item", $comment->item_id);
    access::required("edit", $item);

    $comment->delete();
    print json_encode(array("result" => "success"));
  }

  /**
   * Present a form for adding a new comment to this item or editing an existing comment.
   *  @see REST_Controller::form_add($resource)
   */
  public function _form_add($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);

    print comment::get_add_form($item);
  }

  /**
   * Present a form for editing an existing comment.
   *  @see REST_Controller::form_edit($resource)
   */
  public function _form_edit($comment) {
    print comment::get_edit_form($comment);
  }
}
