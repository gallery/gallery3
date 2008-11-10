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
class Photo_Controller extends Item_Controller {
  public function get($item) {
    $template = new View("page.html");

    /** @todo: this needs to be data-driven */
    $theme = new Theme("default", $template);

    $template->set_global('item', $item);
    $template->set_global('children', $item->children());
    $template->set_global('parents', $item->parents());
    $template->set_global('theme', $theme);
    $template->content = new View("photo.html");

    $login_view = new View("login.html");
    $user = Session::instance()->get("user", null);
    $login_view->logged_in = !empty($user);
    $template->set_global("login", $login_view);

    print $template->render();
  }
}
