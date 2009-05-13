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
class Admin_Comments_Controller extends Admin_Controller {
  private static $items_per_page = 20;

  public function index() {
    // Get rid of old deleted/spam comments once in a while
    Database::instance()->query(
      "DELETE FROM {comments} " .
      "WHERE state IN ('deleted', 'spam') " .
      "AND unix_timestamp(now()) - updated > 86400 * 7");

    // Redirect to the appropriate queue
    url::redirect("admin/comments/queue/unpublished");
  }

  public function menu_labels() {
    $menu = $this->_menu($this->_counts());
    print json_encode(array($menu->get("unpublished")->label,
                            $menu->get("published")->label,
                            $menu->get("spam")->label,
                            $menu->get("deleted")->label));
  }

  public function queue($state) {
    $page = max(Input::instance()->get("page"), 1);

    $view = new Admin_View("admin.html");
    $view->content = new View("admin_comments.html");
    $view->content->counts = $this->_counts();
    $view->content->menu = $this->_menu($view->content->counts);
    $view->content->state = $state;
    $view->content->comments = ORM::factory("comment")
      ->orderby("created", "DESC")
      ->where("state", $state)
      ->limit(self::$items_per_page, ($page - 1) * self::$items_per_page)
      ->find_all();
    $view->content->pager = new Pagination();
    $view->content->pager->initialize(
      array("query_string" => "page",
            "total_items" => $view->content->counts->$state,
            "items_per_page" => self::$items_per_page,
            "style" => "classic"));

    print $view;
  }

  private function _menu($counts) {
    return Menu::factory("root")
      ->append(Menu::factory("link")
               ->id("unpublished")
               ->label(t2("Awaiting Moderation (%count)",
                          "Awaiting Moderation (%count)",
                          $counts->unpublished))
               ->url(url::site("admin/comments/queue/unpublished")))
      ->append(Menu::factory("link")
               ->id("published")
               ->label(t2("Approved (%count)",
                          "Approved (%count)",
                          $counts->published))
               ->url(url::site("admin/comments/queue/published")))
      ->append(Menu::factory("link")
               ->id("spam")
               ->label(t2("Spam (%count)",
                          "Spam (%count)",
                          $counts->spam))
               ->url(url::site("admin/comments/queue/spam")))
      ->append(Menu::factory("link")
               ->id("deleted")
               ->label(t2("Recently Deleted (%count)",
                          "Recently Deleted (%count)",
                          $counts->deleted))
               ->url(url::site("admin/comments/queue/deleted")));
  }

  private function _counts() {
    $counts->unpublished = 0;
    $counts->published = 0;
    $counts->spam = 0;
    $counts->deleted = 0;
    foreach (Database::instance()
             ->select("state", "count(*) as c")
             ->from("comments")
             ->groupby("state")
             ->get() as $row) {
      $counts->{$row->state} = $row->c;
    }
    return $counts;
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

