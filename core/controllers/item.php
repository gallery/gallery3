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
class Item_Controller extends Controller {

  public function dispatch($id) {
    /** @todo this needs security checks */
    $item = ORM::factory("item")->where("id", $id)->find();
    if (empty($item->id)) {
      return Kohana::show_404();
    }

    /**
     * We're expecting to run in an environment that only supports GET/POST, so expect to tunnel
     * PUT/DELETE through POST.
     */
    if (request::method() == 'get') {
      $this->get($item);

      if (Session::instance()->get("use_profiler", false)) {
        $profiler = new Profiler();
        print $profiler->render();
      }
      return;
    }

    switch ($this->input->post('__action')) {
    case 'put':
      return $this->put($item);

    case 'delete':
      return $this->delete($item);

    default:
      return $this->post($item);
    }
  }

  public function get($item) {
    // Redirect to the more specific resource type, since it will render
    // differently.  We could also just delegate here, but it feels more appropriate
    // to have a single canonical resource mapping.
    return url::redirect("{$item->type}/$item->id");
  }

  public function put($item) {
  }

  public function delete($item) {
    /** @todo: needs security checks */
    $parent = $item->parent();
    if ($parent->id) {
      $item->delete();
    }
    url::redirect("{$parent->type}/{$parent->id}");
  }

  public function post($item) {
    /** @todo Productionize this. */
    // 1) Figure out how to do the right validation here.  Validate the form input and apply it to
    //    the model as appropriate.
    // 2) Figure out how to dispatch according to the needs of the client.  Ajax requests from
    //    jeditable will want the changed field back, and possibly the whole item in json.
    //
    // For now let's establish a simple protocol where the client passes in a __return parameter
    // that specifies which field it wants back from the item.  Later on we can expand that to
    // include a data format, etc.

    $post = $this->input->post();
    foreach (array("title", "description") as $field) {
      if (array_key_exists($field, $post)) {
        $value = $item->$field = $post[$field];
      }
    }
    $item->save();
    print $item->{$post['__return']};
  }
}
