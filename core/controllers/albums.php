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
class Albums_Controller extends Items_Controller {

  /**
   *  @see REST_Controller::_show($resource)
   */
  public function _show($album) {
    access::required("view", $album);

    $page_size = module::get_var("core", "page_size", 9);
    $page = $this->input->get("page", "1");
    $children_count = $album->viewable()->children_count();
    $offset = ($page-1) * $page_size;

    // Make sure that the page references a valid offset
    if ($page < 1 || $page > max(ceil($children_count / $page_size), 1)) {
      Kohana::show_404();
    }

    $template = new Theme_View("page.html", "album");
    $template->set_global("page_size", $page_size);
    $template->set_global("item", $album);
    $template->set_global("children", $album->viewable()->children($page_size, $offset));
    $template->set_global("children_count", $children_count);
    $template->set_global("parents", $album->parents());
    $template->content = new View("album.html");

    $album->view_count++;
    $album->save();

    print $template;
  }

  /**
   * @see REST_Controller::_create($resource)
   */
  public function _create($album) {
    access::required("edit", $album);

    switch ($this->input->post("type")) {
    case "album":
      return $this->_create_album($album);

    case "photo":
      return $this->_create_photo($album);

    default:
      access::forbidden();
    }
  }

  private function _create_album($album) {
    access::required("edit", $album);

    rest::http_content_type(rest::JSON);
    $form = album::get_add_form($album);
    if ($form->validate()) {
      $new_album = album::create(
        $album,
        $this->input->post("name"),
        $this->input->post("title", $this->input->post("name")),
        $this->input->post("description"),
        user::active()->id);

      log::success("content", "Created an album",
               html::anchor("albums/$new_album->id", "view album"));
      message::success(sprintf(_("Created album %s"), $new_album->title));

      print json_encode(
        array("result" => "success",
              "location" => url::site("albums/$new_album->id"),
              "resource" => url::site("albums/$new_album->id")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  private function _create_photo($album) {
    access::required("edit", $album);

    rest::http_content_type(rest::JSON);
    $form = photo::get_add_form($album);
    if ($form->validate()) {
      $photo = photo::create(
        $album,
        $this->input->post("file"),
        $_FILES["file"]["name"],
        $this->input->post("title", $this->input->post("name")),
        $this->input->post("description"),
        user::active()->id);

      log::success("content", "Added a photo",
               html::anchor("photos/$photo->id", "view photo"));
      message::add(sprintf(_("Added photo %s"), $photo->title));

      print json_encode(
        array("result" => "success",
              "resource" => url::site("photos/$photo->id"),
              "location" => url::site("photos/$photo->id")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  /**
   * @see REST_Controller::_update($resource)
   */
  public function _update($album) {
    access::required("edit", $album);

    rest::http_content_type(rest::JSON);
    $form = album::get_edit_form($album);
    if ($form->validate()) {
      // @todo implement changing the name.  This is not trivial, we have
      // to check for conflicts and rename the album itself, etc.  Needs an
      // api method.
      $album->title = $form->edit_album->title->value;
      $album->description = $form->edit_album->description->value;
      $album->save();

      module::event("album_changed", $album);

      log::success("content", "Updated album", "<a href=\"albums/$album->id\">view</a>");
      message::success(sprintf(_("Saved album %s"), $album->title));

      print json_encode(
        array("result" => "success",
              "location" => url::site("albums/$album->id")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  /**
   *  @see REST_Controller::_form_add($parameters)
   */
  public function _form_add($album_id) {
    $album = ORM::factory("item", $album_id);
    access::required("edit", $album);

    switch ($this->input->get("type")) {
    case "album":
      print album::get_add_form($album);
      break;

    case "photo":
      print photo::get_add_form($album);
      break;

    default:
      kohana::show_404();
    }
  }

  /**
   *  @see REST_Controller::_form_add($parameters)
   */
  public function _form_edit($album) {
    access::required("edit", $album);

    print album::get_edit_form($album);
  }
}
