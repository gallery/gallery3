<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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
class Comment_Controller_Admin_ManageComments extends Controller_Admin {
  private static $items_per_page = 20;

  public function action_index() {
    // Get rid of old deleted/spam comments once in a while
    DB::delete("comments")
      ->where("state", "IN", array("deleted", "spam"))
      ->where("updated", "<", DB::expr("UNIX_TIMESTAMP() - 86400 * 7"))
      ->execute();

    $view = new View_Admin("required/admin.html");
    $view->content = new View("admin/manage_comments.html");
    $view->content->menu = $this->_menu($this->_counts());
    print $view;
  }

  public function action_menu_labels() {
    $menu = $this->_menu($this->_counts());
    JSON::reply(array((string) $menu->get("unpublished")->label,
                      (string) $menu->get("published")->label,
                      (string) $menu->get("spam")->label,
                      (string) $menu->get("deleted")->label));
  }

  public function action_queue($state) {
    $page = max(Request::$current->query("page"), 1);

    $view = new View_Gallery("admin/manage_comments_queue.html");
    $view->counts = $this->_counts();
    $view->menu = $this->_menu($view->counts);
    $view->state = $state;
    $view->comments = ORM::factory("Comment")
      ->order_by("created", "DESC")
      ->order_by("id", "DESC")
      ->where("state", "=", $state)
      ->limit(self::$items_per_page)
      ->offset(($page - 1) * self::$items_per_page)
      ->find_all();

    // This view is not themed so we can't use $theme->url() in the view and have to
    // reproduce View_Gallery::url() logic here.
    $atn = Theme::$admin_theme_name;
    $view->fallback_avatar_url = URL::abs_file("themes/$atn/assets/required/avatar.jpg");

    $view->page = $page;
    $view->page_type = "collection";
    $view->page_subtype = "admin_comments";
    $view->page_size = self::$items_per_page;
    $view->children_count = $this->_counts()->$state;
    $view->max_pages = ceil($view->children_count / $view->page_size);

    // Also we want to use $theme->paginator() so we need a dummy theme
    $view->theme = $view;

    print $view;
  }

  private function _menu($counts) {
    return Menu::factory("root")
      ->append(Menu::factory("link")
               ->id("unpublished")
               ->label(t2("Awaiting Moderation (%count)",
                          "Awaiting Moderation (%count)",
                          $counts->unpublished))
               ->url(URL::site("admin/manage_comments/queue/unpublished")))
      ->append(Menu::factory("link")
               ->id("published")
               ->label(t2("Approved (%count)",
                          "Approved (%count)",
                          $counts->published))
               ->url(URL::site("admin/manage_comments/queue/published")))
      ->append(Menu::factory("link")
               ->id("spam")
               ->label(t2("Spam (%count)",
                          "Spam (%count)",
                          $counts->spam))
               ->url(URL::site("admin/manage_comments/queue/spam")))
      ->append(Menu::factory("link")
               ->id("deleted")
               ->label(t2("Recently Deleted (%count)",
                          "Recently Deleted (%count)",
                          $counts->deleted))
               ->url(URL::site("admin/manage_comments/queue/deleted")));
  }

  private function _counts() {
    $counts = new stdClass();
    $counts->unpublished = 0;
    $counts->published = 0;
    $counts->spam = 0;
    $counts->deleted = 0;
    foreach (DB::select("state")
             ->select(array("c" => 'COUNT("*")'))
             ->from("comments")
             ->group_by("state")
             ->execute() as $row) {
      $counts->{$row->state} = $row->c;
    }
    return $counts;
  }

  public function action_set_state($id, $state) {
    Access::verify_csrf();

    $comment = ORM::factory("Comment", $id);
    $orig = clone $comment;
    if ($comment->loaded()) {
      $comment->state = $state;
      $comment->save();
    }
  }

  public function action_delete_all_spam() {
    Access::verify_csrf();

    DB::delete("comments")
      ->where("state", "=", "spam")
      ->execute();
    HTTP::redirect("admin/manage_comments/queue/spam");
  }
}
