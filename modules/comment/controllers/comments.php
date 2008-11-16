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
  protected $resource_type = "item";

  /**
   * Present a form for adding a new comment to this item
   *  @see Rest_Controller::form($resource)
   */
  public function _form($item) {
    $form = comment::get_add_form($item);
    print $form->render("form.html");
  }

  /**
   * Show the comment collection
   *  @see Rest_Controller::_get($resource)
   */
  public function _get($item) {
    print comment::get_comments($item);
  }

  /**
   * Update existing comment collection.
   *  @see Rest_Controller::_put($resource)
   */
  public function _put($item) {
    throw new Exception("@todo Comment_Controller::_put NOT IMPLEMENTED");
  }

  /**
   * Add a new comment to the collection
   * @see Rest_Controller::_post($resource)
   */
  public function _post($item) {
    $form = comment::get_add_form($item);
    if ($form->validate()) {
      $comment = ORM::factory('comment');
      $comment->author = $this->input->post('author');
      $comment->email = $this->input->post('email');
      $comment->text = $this->input->post('text');
      $comment->datetime = time();
      $comment->item_id = $item->id;
      $comment->save();

      header("HTTP/1.1 201 Created");
      header("Location: " . url::site("comment/{$comment->id}"));
    }
    print $form->render("form.html");
  }

  /**
   * Delete existing comment collection.
   *  @see Rest_Controller::_delete($resource)
   */
  public function _delete($item) {
    throw new Exception("@todo Comment_Controller::_delete NOT IMPLEMENTED");
  }
}
