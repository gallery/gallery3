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
class Admin_Comments_Controller extends Admin_Controller {

  public function index() {
     $this->queue("all");
  }

  public function queue($state) {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_comments.html");
    $view->content->all = $this->_query(array("published", "unpublished"));
    $view->content->published = $this->_query(array("published"));
    $view->content->unpublished = $this->_query(array("unpublished"));
    $view->content->spam = $this->_query(array("spam"));
    $view->content->menu = Menu::factory("root")
      ->append(Menu::factory("link")
               ->id("all")
               ->label(t(array("one" => "All Comments ({{count}})",
                               "other" => "All Comments ({{count}})"),
                         array("count" => $view->content->all->count())))
               ->url(url::site("admin/comments/queue/all")))
      ->append(Menu::factory("link")
               ->id("unpublished")
               ->label(t(array("one" => "Awaiting Moderation ({{count}})",
                               "other" => "Awaiting Moderation ({{count}})"),
                         array("count" => $view->content->unpublished->count())))
               ->url(url::site("admin/comments/queue/unpublished")))
      ->append(Menu::factory("link")
               ->id("published")
               ->label(t(array("one" => "Approved ({{count}})",
                               "other" => "Approved ({{count}})"),
                         array("count" => $view->content->published->count())))
               ->url(url::site("admin/comments/queue/published")))
      ->append(Menu::factory("link")
               ->id("spam")
               ->label(t(array("one" => "Spam ({{count}})",
                               "other" => "Spam ({{count}})"),
                         array("count" => $view->content->spam->count())))
               ->url(url::site("admin/comments/queue/spam")));

    switch ($state) {
    case "all":
      $view->content->comments = $view->content->all;
      $view->content->title = t("All Comments");
      break;

    case "published":
      $view->content->comments = $view->content->published;
      $view->content->title = t("Approved Comments");
      break;

    case "unpublished":
      $view->content->comments = $view->content->unpublished;
      $view->content->title = t("Comments Awaiting Moderation");
      break;

    case "spam":
      $view->content->title = t("Spam Comments");
      $view->content->comments = $view->content->spam;
      $view->content->spam_caught = module::get_var("comment", "spam_caught");
      break;
    }

    $view->content->queue = $state;
    $view->content->pager = new Pagination();
    $view->content->pager->initialize(
      array('query_string' => 'page',
            'total_items' => $view->content->comments->count(),
            'items_per_page' => 20,
            'style' => 'classic'));

    print $view;
  }

  private function _query($states) {
    $query = ORM::factory("comment")
      ->orderby("created", "DESC");
    if ($states) {
      $query->in("state", $states);
    }
    return $query->find_all();
  }

  public function set_state($id, $state) {
    access::verify_csrf();
    $comment = ORM::factory("comment", $id);
    $orig = clone $comment;
    if ($comment->loaded) {
      $comment->state = $state;
      $comment->save();
      module::event("comment_changed", $orig, $comment);
    }
  }

  public function delete($id) {
    access::verify_csrf();
    $comment = ORM::factory("comment", $id);
    if ($comment->loaded) {
      module::event("comment_before_delete", $comment);
      $comment->delete();
    }
  }

  public function delete_all_spam() {
    access::verify_csrf();
    ORM::factory("comment")
      ->where("state", "spam")
      ->delete_all();
    url::redirect("admin/comments/queue/spam");
  }
}

