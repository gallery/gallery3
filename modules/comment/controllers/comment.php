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
class Comment_Controller extends REST_Controller {
  protected $resource_type = "comment";

  /**
   * Present a form for editing a comment
   *  @see Rest_Controller::form($resource)
   */
  public function _form($comment) {
    $form = comment::get_edit_form($comment);
    print $form;
  }

  /**
   * Get an existing comment.
   *  @see Rest_Controller::_get($resource)
   */
  public function _get($comment) {
    $v = new View("comment.html");
    $v->comment = $comment;
    print $v;
  }

  /**
   * Update existing comment.
   *  @see Rest_Controller::_put($resource)
   */
  public function _put($comment) {
    $form = comment::get_edit_form($comment);
    if ($form->validate()) {
      $comment = ORM::factory('comment');
      $comment->author = $this->input->post('author');
      $comment->email = $this->input->post('email');
      $comment->text = $this->input->post('text');
      $comment->save();
      return;
    }
    print $form;
  }

  /**
   * Add a new comment
   *  @see Rest_Controller::_post($resource)
   */
  public function _post($comment) {
    throw new Exception("@todo Comment_Controller::_post NOT IMPLEMENTED");
  }

  /**
   * Delete existing comment.
   *  @see Rest_Controller::_delete($resource)
   */
  public function _delete($resource) {
    throw new Exception("@todo Comment_Controller::_delete NOT IMPLEMENTED");
  }
}
