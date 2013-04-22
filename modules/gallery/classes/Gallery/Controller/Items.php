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
class Gallery_Controller_Items extends Controller {
  public function __call($function, $args) {
    $item = ORM::factory("Item", (int)$function);
    if (!$item->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    // Redirect to the more specific resource type, since it will render differently.  We can't
    // delegate here because we may have gotten to this page via /items/<id> which means that we
    // don't have a type-specific controller.  Also, we want to drive a single canonical resource
    // mapping where possible.
    Access::required("view", $item);
    $this->redirect($item->abs_url());
  }

  // Return the width/height dimensions for the given item
  public function action_dimensions() {
    $id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);
    $this->response->json(array("thumb" => array((int)$item->thumb_width, (int)$item->thumb_height),
                                "resize" => array((int)$item->resize_width, (int)$item->resize_height),
                                "full" => array((int)$item->width, (int)$item->height)));
  }
}
