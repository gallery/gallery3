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
    // See if we got here via items/show/<id> - if so, do a 301 redirect to our canonical URL.
    if ($item_id = $this->request->arg_optional(0)) {
      $item = ORM::factory("Item", $item_id);
      if (!$item->loaded()) {
        throw HTTP_Exception::factory(404);
      }
      Access::required("view", $item);
      $this->redirect($item->abs_url(), 301);
    }

    // Find the item by its URL, check access, and increment the view count.
    $item_url = $this->request->param("item_url");
    if (empty($item_url)) {
      $item = Item::root();
    } else {
      $item = Item::find_by_relative_url($item_url);
      if (!$item->loaded()) {
        throw HTTP_Exception::factory(404);
      }
    }
    Access::required("view", $item);
    $item->increment_view_count();

    // Build the view.  Photos and movies are nearly identical, but albums are different.
    if ($item->is_album()) {
      $page_size = Module::get_var("gallery", "page_size", 9);
      $show = $this->request->query("show");

      if ($show) {
        $child = ORM::factory("Item", $show);
        $index = Item::get_position($child);
        if ($index) {
          $page = ceil($index / $page_size);
          if ($page == 1) {
            $this->redirect($item->abs_url());
          } else {
            $this->redirect($item->abs_url("page=$page"));
          }
        }
      }

      $page = Arr::get($this->request->query(), "page", "1");
      $children_count = $item->children->viewable()->count_all();
      $offset = ($page - 1) * $page_size;
      $max_pages = max(ceil($children_count / $page_size), 1);

      // Make sure that the page references a valid offset
      if ($page < 1) {
        $this->redirect($item->abs_url());
      } else if ($page > $max_pages) {
        $this->redirect($item->abs_url("page=$max_pages"));
      }

      $template = new View_Theme("required/page.html", "collection", "album");
      $template->content = new View("required/album.html");
      $template->set_global(array(
        "page" => $page,
        "page_title" => null,
        "max_pages" => $max_pages,
        "page_size" => $page_size,
        "item" => $item,
        "children" => $item->children->viewable()->limit($page_size)->offset($offset)->find_all(),
        "parents" => $item->parents->find_all()->as_array(), // view calls empty() on this
        "breadcrumbs" => Breadcrumb::array_from_item_parents($item),
        "children_count" => $children_count
      ));
      Item::set_display_context_callback("Controller_Albums::get_display_context");
    } else {
      $template = new View_Theme("required/page.html", "item", $item->type);
      $template->content = new View("required/{$item->type}.html");
      $template->set_global(array(
        "item" => $item,
        "children" => array(),
        "children_count" => 0
      ));
      $template->set_global(Item::get_display_context($item));
    }

    $this->response->body($template);
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

  public function action_rotate() {
    Access::verify_csrf();

    $item_id = $this->request->arg(0, "digit");
    $dir = $this->request->arg(1, "alpha");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    switch($dir) {
      case "ccw": $degrees = -90; break;
      case "cw":  $degrees =  90; break;
      default:    throw HTTP_Exception::factory(400);
    }

    // Get the from_id query parameter, which defaults to the edited item's id.
    $from_id = Arr::get($this->request->query(), "from_id", $item->id);

    $tmpfile = System::temp_filename("rotate", pathinfo($item->file_path(), PATHINFO_EXTENSION));
    GalleryGraphics::rotate($item->file_path(), $tmpfile, array("degrees" => $degrees), $item);
    $item->set_data_file($tmpfile);
    $item->save();

    // We don't need to refresh the page - just tell js what the new dimensions are.
    if ($from_id == $item->id) {
      $this->response->json(
        array("src" => $item->resize_url(),
              "width" => $item->resize_width,
              "height" => $item->resize_height));
    } else {
      $this->response->json(
        array("src" => $item->thumb_url(),
              "width" => $item->thumb_width,
              "height" => $item->thumb_height));
    }
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
