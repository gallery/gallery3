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
class Tags_Controller extends REST_Controller {
  protected $resource_type = "tag";

  public function _show($tag) {
    // @todo: these need to be pulled from the database
    $theme_name = "default";
    $page_size = 9;

    $template = new View("page.html");

    $page = $this->input->get("page", "1");
    $theme = new Theme($theme_name, $template);

    $template->set_global("page_type", "tag");
    $template->set_global('page_size', $page_size);
    $template->set_global('tag', $tag);
    $template->set_global('children', $tag->items($page_size, ($page-1) * $page_size));
    $template->set_global('children_count', $tag->count);
    $template->set_global('theme', $theme);
    $template->set_global('user', Session::instance()->get('user', null));
    $template->content = new View("tag.html");

    print $template;
  }

  public function _index() {
    throw new Exception("@todo Tag_Controller::_index NOT IMPLEMENTED");
  }

  public function _form_add($parameters) {
    $item_id = is_array($parameters) ? $parameters[0] : $parameters;
    $form = tag::get_add_form($item_id);
    if ($form->validate()) {
      $tags = explode(",", $form->inputs["tags"]->value);

//      $item = ORM::factory("item", $item_id);
      $form->inputs["tags"]->value("add new tags...");
    }
    print $form->render();
  }

  public function _form_edit($tag) {
    throw new Exception("@todo Tag_Controller::_form_edit NOT IMPLEMENTED");
  }

  public function _create($tag) {
    throw new Exception("@todo Tag_Controller::_create NOT IMPLEMENTED");
  }

  public function _delete($tag) {
    throw new Exception("@todo Tag_Controller::_delete NOT IMPLEMENTED");
  }

  public function _update($tag) {
    throw new Exception("@todo Tag_Controller::_update NOT IMPLEMENTED");
  }
}
