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
    $theme_name = module::get_var("core", "active_theme", "default");
    $page_size = module::get_var("core", "page_size", 9);

    $template = new Theme_View("page.html", "tag", $theme_name);

    $page = $this->input->get("page", "1");

    $template->set_global('page_size', $page_size);
    $template->set_global('tag', $tag);
    $template->set_global('children', $tag->items($page_size, ($page-1) * $page_size));
    $template->set_global('children_count', $tag->count);
    $template->content = new View("tag.html");

    print $template;
  }

  public function _index() {
    // @todo: represent this in different formats
    print tag::cloud(30);
  }

  public function _form_add($item_id) {
    return tag::get_add_form($item_id);
  }

  public function _form_edit($tag) {
    throw new Exception("@todo Tag_Controller::_form_edit NOT IMPLEMENTED");
  }

  public function _create($tag) {
    $form = tag::get_add_form($this->input->post('item_id'));
    if ($form->validate()) {
      $item = ORM::factory("item", $this->input->post("item_id"));
      if (access::can("edit", $item)) {
        tag::add($item, $this->input->post("tag_name"));
        rest::http_status(rest::CREATED);
        rest::http_location(url::site("tags/{$tag->id}"));
      } else {
        $form->inputs["add_tag"]->inputs["tag_name"]->add_error("permission denied", 1);
      }
    }

    print $form;
  }

  public function _delete($tag) {
    throw new Exception("@todo Tag_Controller::_delete NOT IMPLEMENTED");
  }

  public function _update($tag) {
    throw new Exception("@todo Tag_Controller::_update NOT IMPLEMENTED");
  }
}
