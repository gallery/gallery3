<?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class Admin_Dynamic_Controller extends Admin_Controller {
  public function index() {
    print $this->_get_view();
  }

  public function handler() {
    access::verify_csrf();

    $form = $this->_get_form();
    if ($form->validate()) {
      foreach (array("updates", "popular") as $album) {
        $album_defn = unserialize(module::get_var("dynamic", $album));
        $group = $form->inputs[$album];
        $album_defn->enabled = $group->inputs["{$album}_enabled"]->value;
        $album_defn->description = $group->inputs["{$album}_description"]->value;
        $album_defn->limit = $group->inputs["{$album}_limit"] === "" ? null :
          $group->inputs["{$album}_limit"]->value;
        module::set_var("dynamic", $album, serialize($album_defn));
      }

      message::success(t("Dynamic Albums Configured"));

      url::redirect("admin/dynamic");
    }

    print $this->_get_view($form);
  }

  private function _get_view($form=null) {
    $v = new Admin_View("admin.html");
    $v->content = new View("admin_dynamic.html");
    $v->content->form = empty($form) ? $this->_get_form() : $form;
    return $v;
  }

  private function _get_form() {

    $form = new Forge("admin/dynamic/handler", "", "post",
                      array("id" => "gAdminForm"));

    foreach (array("updates", "popular") as $album) {
      $album_defn = unserialize(module::get_var("dynamic", $album));

      $group = $form->group($album)->label(t($album_defn->title));
      $group->checkbox("{$album}_enabled")
        ->label(t("Enable"))
        ->value(1)
        ->checked($album_defn->enabled);
      $group->input("{$album}_limit")
        ->label(t("Limit (leave empty for unlimited)"))
        ->value(empty($album_defn->limit) ? "" : $album_defn->limit)
        ->rules("valid_numeric");
      $group->textarea("{$album}_description")
        ->label(t("Description"))
        ->rules("length[0,2048]")
        ->value($album_defn->description);
    }

    $form->submit("submit")->value(t("Submit"));

    return $form;
  }
}