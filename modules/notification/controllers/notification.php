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
class Notification_Controller extends Controller {
  function watch($id) {
    $item = ORM::factory("item", $id);
    access::required("view", $item);

    $watching = notification::is_watching($item);
    $form = $this->_get_form($item, $watching);
    if (request::method() == "post") {
      if ($form->validate()) {
        if (!$watching) {
          notification::add_watch($item);
          message::success(sprintf(t("Watch Enabled on %s!"), $item->title));
          $response = json_encode(array("result" => "success"));
        } else {
          notification::remove_watch($item);
          $response = json_encode(array("result" => "success"));
          message::success(sprintf(t("Watch Removed on %s!"), $item->title));
        }
      } else {
        $response = json_encode(array("result" => "error", "form" => $form->__toString()));
      }
    } else {
      $response = $form;
    }

    print $response;
  }

  function _get_form($item, $watching) {
    $button_text = $watching ? t("Remove Watch") : t("Add Watch");
    $label = $watching ? t("Remove Watch from Album") : t("Add Watch to Album");

    $form = new Forge("notification/watch/$item->id", "", "post", array("id" => "gAddWatchForm"));
    $group = $form->group("watch")->label($label);
    $group->submit("")->value($button_text);

    return $form;
  }
}
