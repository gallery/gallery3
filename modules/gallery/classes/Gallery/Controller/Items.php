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
  public function action_show() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    if (!$item->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    // Redirect to the more specific resource type, since it will render differently.  We can't
    // delegate here because we may have gotten to this page via items/show/<id> which means that we
    // don't have a type-specific controller.  Also, we want to drive a single canonical resource
    // mapping where possible.
    Access::required("view", $item);
    $this->redirect($item->abs_url(), 301);
  }

  public function action_delete() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    // Get the from_id query parameter, which defaults to the edited item's id.
    $from_id = Arr::get($this->request->query(), "from_id", $item->id);

    $form = Formo::form()
      ->attr("id", "g-delete-item-form")
      ->add("from_id", "input|hidden", $from_id)
      ->add("confirm", "group")
      ->add_script_text(
          '$("#g-delete-item-form").submit(function() {
            $("#g-delete-item-form input[type=submit]").gallery_show_loading();
          });'
        );  // @todo: make all dialogs do something like this automatically.
    $form->confirm
      ->set("label", t("Confirm Deletion"))
      ->html($item->is_album() ?
          t("Delete the album <b>%title</b>? All photos and movies in the album will also be deleted.",
            array("title" => HTML::purify($item->title))) :
          t("Are you sure you want to delete <b>%title</b>?",
            array("title" => HTML::purify($item->title)))
        )
      ->add("submit", "input|submit", t("Delete"));

    if ($form->sent()) {
      if ($form->load()->validate()) {
        $msg = Arr::get(array(
          "album" => t("Deleted album <b>%title</b>", array("title" => HTML::purify($item->title))),
          "photo" => t("Deleted photo <b>%title</b>", array("title" => HTML::purify($item->title))),
          "movie" => t("Deleted movie <b>%title</b>", array("title" => HTML::purify($item->title)))
        ), $item->type);

        // If we just deleted the item we were viewing, we'll need to redirect to the parent.
        $location = ($form->from_id->val() == $item->id) ? $item->parent->url() : null;

        if ($item->is_album()) {
          // Album delete will trigger deletes for all children.  Do this in a batch so that we can
          // be smart about notifications, album cover updates, etc.
          Batch::start();
          $item->delete();
          Batch::stop();
        } else {
          $item->delete();
        }

        Message::success($msg);

        if (isset($location)) {
          $this->response->json(array("result" => "success", "location" => $location));
        } else {
          $this->response->json(array("result" => "success", "reload" => 1));
        }
      } else {
        $this->response->json(array("result" => "error", "html" => (string)$form));
      }
      return;
    }

    $this->response->body($form);
  }

  public function action_make_album_cover() {
    Access::verify_csrf();

    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("view", $item->parent);
    Access::required("edit", $item->parent);

    $msg = t("Made <b>%title</b> this album's cover", array("title" => HTML::purify($item->title)));

    Item::make_album_cover($item);
    Message::success($msg);

    $this->response->json(array("result" => "success", "reload" => 1));
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
