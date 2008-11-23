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
    $item_id = $this->input->get('item_id');

    if (empty($item_id)) {
      /* We currently do not support getting all comments from the entire gallery. */
      rest::http_status(rest::BAD_REQUEST);
      return;
    }
    print comment::get_comments($item_id);
  }

  /**
   * Add a new comment to the collection.
   * @see Rest_Controller::_create($resource)
   */
  public function _create($comment) {
    $form = comment::get_add_form($this->input->post('item_id'));
    if ($form->validate()) {
      $comment->author = $this->input->post('author');
      $comment->email = $this->input->post('email');
      $comment->text = $this->input->post('text');
      $comment->datetime = time();
      $comment->item_id = $this->input->post('item_id');
      $comment->save();

      rest::http_status(rest::CREATED);
      rest::http_location(url::site("comments/{$comment->id}"));
    }
    // @todo Return appropriate HTTP status code indicating error.
    print $form;
  }

  /**
   * Display an existing comment.
   *  @todo Set proper Content-Type in a central place (REST_Controller::dispatch?).
   *  @see Rest_Controller::_show($resource)
   */
  public function _show($comment) {
    $output_format = rest::output_format();
    switch ($output_format) {
    case "xml":
      rest::http_content_type(rest::XML);
      print xml::to_xml($comment->as_array(), array("comment"));
      break;

    case "json":
      rest::http_content_type(rest::JSON);
      print json_encode($comment->as_array());
      break;

    case "atom":
      rest::http_content_type(rest::XML);
      print comment::get_atom_entry($comment);
      break;

    default:
      $v = new View("comment.$output_format");
      $v->comment = $comment;
      print $v;
    }
  }

  /**
   * Change an existing comment.
   *  @see Rest_Controller::_update($resource)
   */
  public function _update($comment) {
    $form = comment::get_edit_form($comment);
    if ($form->validate()) {
      $comment->author = $this->input->post('author');
      $comment->email = $this->input->post('email');
      $comment->text = $this->input->post('text');
      $comment->save();
      return;
    }
    // @todo Return appropriate HTTP status code indicating error.
    print $form;
  }

  /**
   * Delete existing comment.
   *  @see Rest_Controller::_delete($resource)
   */
  public function _delete($comment) {
    $comment->delete();
    rest::http_status(rest::OK);
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
    print $form = comment::get_edit_form($comment);
  }
}
