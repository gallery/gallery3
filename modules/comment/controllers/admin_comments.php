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

  private function _get_base_view($state) {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_comments.html");
    $view->content->menu = Menu::factory("root")
      ->append(Menu::factory("link")
               ->id("published")
               ->label(_("Published"))
               ->url(url::site("admin/comments/published")))
      ->append(Menu::factory("link")
               ->id("unpublished")
               ->label(_("Unpublished"))
               ->url(url::site("admin/comments/unpublished")))
      ->append(Menu::factory("link")
               ->id("spam")
               ->label(_("Spam"))
               ->url(url::site("admin/comments/spam")));
    $view->content->comments = ORM::factory("comment")
      ->where("state", $state)
      ->orderby("created", "DESC")
      ->find_all();

    return $view;
  }

  public function index() {
    return $this->published();
  }

  public function published() {
    print $this->_get_base_view("published");
  }

  public function unpublished() {
    print $this->_get_base_view("unpublished");
  }

  public function spam() {
    print $this->_get_base_view("spam");
  }
}

