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
class Notification_Controller_Notification extends Controller {
  public function action_watch() {
    $id = $this->request->arg(0, "digit");
    Access::verify_csrf();

    $item = ORM::factory("Item", $id);
    Access::required("view", $item);

    if (Notification::is_watching($item)) {
      Notification::remove_watch($item);
      Message::success(sprintf(t("You are no longer watching %s"), HTML::purify($item->title)));
    } else {
      Notification::add_watch($item);
      Message::success(sprintf(t("You are now watching %s"), HTML::purify($item->title)));
    }
    $this->redirect($item->abs_url());
  }
}
