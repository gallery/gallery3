<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Admin_Manage_Comments_Controller extends Admin_Controller {
  private static $items_per_page = 20;

  public function index() {
    // Get rid of old deleted/spam comments once in a while
    db::build()
      ->delete("comments")
      ->where("state", "IN", array("deleted", "spam"))
      ->where("updated", "<", new Database_Expression("UNIX_TIMESTAMP() - 86400 * 7"))
      ->execute();

    // Redirect to the appropriate queue
    url::redirect("admin/manage_comments/queue/unpublished");
  }

  public function menu_labels() {
    $menu = $this->_menu($this->_counts());
    json::reply(array((string) $menu->get("unpublished")->label,
                      (string) $menu->get("published")->label,
                      (string) $menu->get("spam")->label,
                      (string) $menu->get("deleted")->label));
  }

  public function queue($state) {
    $page = max(Input::instance()->get("page"), 1);

    $view = new Admin_View("admin.html");
    $view->page_title = t("Manage comments");
    $view->content = new View("admin_manage_comments.html");
    $view->content->counts = $this->_counts();
    $view->content->menu = $this->_menu($view->content->counts);
    $view->content->state = $state;
    $view->content->comments = ORM::factory("comment")
      ->order_by("created", "DESC")
      ->order_by("id", "DESC")
      ->where("state", "=", $state)
      ->limit(self::$items_per_page)
      ->offset(($page - 1) * self::$items_per_page)
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
               ->url(url::site("admin/manage_comments/queue/unpublished")))
      ->append(Menu::factory("link")
               ->id("published")
               ->label(t2("Approved (%count)",
                          "Approved (%count)",
                          $counts->published))
               ->url(url::site("admin/manage_comments/queue/published")))
      ->append(Menu::factory("link")
               ->id("spam")
               ->label(t2("Spam (%count)",
                          "Spam (%count)",
                          $counts->spam))
               ->url(url::site("admin/manage_comments/queue/spam")))
      ->append(Menu::factory("link")
               ->id("deleted")
               ->label(t2("Recently Deleted (%count)",
                          "Recently Deleted (%count)",
                          $counts->deleted))
               ->url(url::site("admin/manage_comments/queue/deleted")));
  }

  private function _counts() {
    $counts = new stdClass();
    $counts->unpublished = 0;
    $counts->published = 0;
    $counts->spam = 0;
    $counts->deleted = 0;
    foreach (db::build()
             ->select("state")
             ->select(array("c" => 'COUNT("*")'))
             ->from("comments")
             ->group_by("state")
             ->execute() as $row) {
      $counts->{$row->state} = $row->c;
    }
    return $counts;
  }

  public function set_state($id, $state) {
    access::verify_csrf();

    $comment = ORM::factory("comment", $id);
    $orig = clone $comment;
    if ($comment->loaded()) {
      $comment->state = $state;
      $comment->save();
    }
  }

  public function delete_all_spam() {
    access::verify_csrf();

    db::build()
      ->delete("comments")
      ->where("state", "=", "spam")
      ->execute();
    url::redirect("admin/manage_comments/queue/spam");
  }
}

