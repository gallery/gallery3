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
    $page_size = module::get_var("core", "page_size", 9);
    $page = $this->input->get("page", "1");
    $children_count = $tag->items_count();
    $offset = ($page-1) * $page_size;

    // Make sure that the page references a valid offset
    if ($page < 1 || $page > ceil($children_count / $page_size)) {
      Kohana::show_404();
    }

    $template = new Theme_View("page.html", "tag");
    $template->set_global('page_size', $page_size);
    $template->set_global('page_title', t("Browse Tag::%name", array("name" => $tag->name)));
    $template->set_global('tag', $tag);
    $template->set_global('children', $tag->items($page_size, $offset));
    $template->set_global('children_count', $children_count);
    $template->content = new View("dynamic.html");

    print $template;
  }

  public function _index() {
    print tag::cloud(30);
  }

  public function _create($tag) {
    $item = ORM::factory("item", $this->input->post("item_id"));
    access::required("edit", $item);

    $form = tag::get_add_form($item);
    if ($form->validate()) {
      tag::add($item, $form->add_tag->inputs["name"]->value);

      print json_encode(
        array("result" => "success",
              "resource" => url::site("tags/{$tag->id}"),
              "form" => tag::get_add_form($item)->__toString()));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  public function _form_add($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);

    return tag::get_add_form($item);
  }
}
