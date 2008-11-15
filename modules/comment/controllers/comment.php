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
   * Return the form for adding comments.
   */
  public function _get_form($comment) {
    $form = new Forge(url::current(true), "", "post", array("id" => "gComment"));
    $group = $form->group(_("Add Comment"));
    $group->input("author")
      ->label(_("Author"))
      ->id("gAuthor")
      ->class(null)
      ->value($comment->author);
    $group->input("email")
      ->label(_("Email"))
      ->id("gEmail")
      ->class(null)
      ->value($comment->email);
    $group->textarea("text")
      ->label(_("Text"))
      ->id("gText")
      ->class(null)
      ->value($comment->text);
    $group->hidden("item_id")
      ->value($comment->item_id);
    $group->submit(_("Add"));

    $this->_add_validation_rules(ORM::factory("comment")->validation_rules, $form);

    return $form;
  }

  /**
   * @todo Refactor this into a more generic location
   */
  private function _add_validation_rules($rules, $form) {
    foreach ($form->inputs as $name => $input) {
      if (isset($input->inputs)) {
        $this->_add_validation_rules($rules, $input);
      }
      if (isset($rules[$name])) {
        $input->rules($rules[$name]);
      }
    }
  }

  public function add($item_id) {
    $comment = ORM::factory('comment');
    $comment->item_id = $item_id;

    $form = $this->_get_form($comment);
    if ($form->validate()) {
      $comment = ORM::factory('comment');
      $comment->author = $this->input->post('author');
      $comment->email = $this->input->post('email');
      $comment->text = $this->input->post('text');
      $comment->datetime = time();
      $comment->item_id = $this->input->post('item_id');
      $comment->save();
    } else {
      print $form->render("form.html");
    }
  }

  public function get_item_comments($item_id) {
    $v = comment::show_comment_list($item_id);
    print $v;
  }

  /**
   * Get an existing comment.
   *  @see Rest_Controller::_get($resource)
   */
  public function _get($user) {
    throw new Exception("@todo Comment_Controller::_get NOT IMPLEMENTED");
  }

  /**
   * Update existing comment.
   *  @see Rest_Controller::_put($resource)
   */
  public function _put($resource) {
    throw new Exception("@todo Comment_Controller::_put NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_post($resource)
   */
  public function _post($user) {
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
