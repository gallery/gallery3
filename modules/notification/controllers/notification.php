<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Notification_Controller extends Controller {
  function watch($id) {
    access::verify_csrf();

    $item = ORM::factory("item", $id);
    access::required("view", $item);

    if (notification::is_watching($item)) {
      notification::remove_watch($item);
      message::success(sprintf(t("You are no longer watching %s"), html::purify($item->title)));
    } else {
      notification::add_watch($item);
      message::success(sprintf(t("You are now watching %s"), html::purify($item->title)));
    }
    url::redirect($item->abs_url());
  }
}
