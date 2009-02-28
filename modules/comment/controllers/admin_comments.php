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

  private function _get_base_view() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_comments.html");
    $view->content->published = $this->_query(array("published"));
    $view->content->unpublished = $this->_query(array("unpublished"));
    $view->content->spam = $this->_query(array("spam"));
    $view->content->deleted = $this->_query(array("deleted"));
    $view->content->menu = Menu::factory("root")
      ->append(Menu::factory("link")
               ->id("unpublished")
               ->label(t2("Awaiting Moderation (%count)",
                          "Awaiting Moderation (%count)",
                          $view->content->unpublished->count()))
               ->url(url::site("admin/comments/queue/unpublished")))
      ->append(Menu::factory("link")
               ->id("published")
               ->label(t2("Approved (%count)",
                          "Approved (%count)",
                          $view->content->published->count()))
               ->url(url::site("admin/comments/queue/published")))
      ->append(Menu::factory("link")
               ->id("spam")
               ->label(t2("Spam (%count)",
                          "Spam (%count)",
                          $view->content->spam->count()))
               ->url(url::site("admin/comments/queue/spam")))
      ->append(Menu::factory("link")
               ->id("deleted")
               ->label(t2("Recently Deleted (%count)",
                          "Recently Deleted (%count)",
                          $view->content->deleted->count()))
               ->url(url::site("admin/comments/queue/deleted")));
    return $view;
  }

  public function index() {
    // Get rid of old deleted/spam comments
    Database::instance()->query(
      "DELETE FROM {comments} " .
      "WHERE state IN ('deleted', 'spam') " .
      "AND unix_timestamp(now()) - updated > 86400 * 7");

    $this->queue("unpublished");
  }

  public function menu_labels($state) {
    $view = $this->_get_base_view();
    print json_encode(array($view->content->menu->get("unpublished")->label,
                            $view->content->menu->get("published")->label,
                            $view->content->menu->get("spam")->label,
                            $view->content->menu->get("deleted")->label));
  }

  public function queue($state) {
    $view = $this->_get_base_view();

    switch ($state) {
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

    case "deleted":
      $view->content->title = t("Recently Deleted Comments");
      $view->content->comments = $view->content->deleted;
      break;
    }

    $view->content->queue = $state;
    $view->content->pager = new Pagination();
    $view->content->pager->initialize(
      array("query_string" => "page",
            "total_items" => $view->content->comments->count(),
            "items_per_page" => 20,
            "style" => "classic"));

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
      module::event("comment_updated", $orig, $comment);
      if ($orig->state == "published" || $comment->state == "published") {
        module::event("item_related_update", $comment->item());
      }
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

