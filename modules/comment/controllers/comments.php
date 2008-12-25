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
   *  @see Rest_Controller::_index()
   */
  public function _index() {
    $item = ORM::factory("item", $this->input->get('item_id'));
    access::required("view", $item);

    $comments = ORM::factory("comment")
      ->where("item_id", $item->id)
      ->orderby("created", "desc")
      ->find_all();

    switch (rest::output_format()) {
    case "json":
      rest::http_content_type(rest::JSON);
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
   * @see Rest_Controller::_create($resource)
   */
  public function _create($comment) {
    rest::http_content_type(rest::JSON);

    $form = comment::get_add_form($this->input->post("item_id"));
    if ($form->validate()) {
      $comment->author = $this->input->post("author");
      $comment->email = $this->input->post("email");
      $comment->text = $this->input->post("text");
      $comment->created = time();
      $comment->item_id = $this->input->post("item_id");
      $comment->save();

      module::event("comment_created", $comment);

      print json_encode(
        array("result" => "success",
              "resource" => url::site("comments/{$comment->id}"),
              "form" => comment::get_add_form($this->input->post("item_id"))->__toString()));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  /**
   * Display an existing comment.
   *  @todo Set proper Content-Type in a central place (REST_Controller::dispatch?).
   *  @see Rest_Controller::_show($resource)
   */
  public function _show($comment) {
    if (rest::output_format() == "json") {
      print json_encode(array("result" => "success", "resource" => $comment));
    } else {
      $view = new View("comment.html");
      $view->comment = $comment;
      print $view;
    }
  }

  /**
   * Change an existing comment.
   *  @see Rest_Controller::_update($resource)
   */
  public function _update($comment) {
    rest::http_content_type(rest::JSON);

    $form = comment::get_edit_form($comment);
    if ($form->validate()) {
      $comment->author = $this->input->post("author");
      $comment->email = $this->input->post("email");
      $comment->text = $this->input->post("text");
      $comment->save();

      module::event("comment_updated", $comment);

      print json_encode(
        array("result" => "success",
              "resource" => url::site("comments/{$comment->id}")));
    } else {
      print json_encode(
        array("result" => "error",
              "html" => $form));
    }
  }

  /**
   * Delete existing comment.
   *  @see Rest_Controller::_delete($resource)
   */
  public function _delete($comment) {
    rest::http_content_type(rest::JSON);

    $comment->delete();
    print json_encode(
      array("result" => "success"));
  }

  /**
   * Present a form for adding a new comment to this item or editing an existing comment.
   *  @see Rest_Controller::form_add($resource)
   */
  public function _form_add($item_id) {
    print comment::get_add_form($item_id);
  }

  /**
   * Present a form for editing an existing comment.
   *  @see Rest_Controller::form_edit($resource)
   */
  public function _form_edit($comment) {
    print comment::get_edit_form($comment);
  }
}
