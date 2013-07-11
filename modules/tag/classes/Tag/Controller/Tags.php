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
class Tag_Controller_Tags extends Controller {
  /**
   * Show a tag cloud.
   */
  public function action_index() {
    // Far from perfection, but at least require view permission for the root album
    Access::required("view", Item::root());

    $this->response->body(Tag::cloud(Module::get_var("tag", "tag_cloud_size", 30)));
  }

  /**
   * Show a tag's items.  This finds the tag by its URL and generates a view.
   */
  public function action_show() {
    // See if we got here via "tags/show/<id>" - if so, fire a 301.
    if ($tag_id = $this->request->arg_optional(0)) {
      $tag = ORM::factory("Tag", $tag_id);
      if (!$tag->loaded()) {
        throw HTTP_Exception::factory(404);
      }
      $this->redirect($tag->abs_url(), 301);
    }

    $tag_url = $this->request->param("tag_url");
    if (empty($tag_url)) {
      // @todo: this is the future home of the album of all tags.  For now, we 404.
      throw HTTP_Exception::factory(404);
    }

    // See if we have a slash in the URL, which might be a Gallery 3.0.x canonical URL with
    // the form "tag/<id>/<name>" - if so, fire a 301.
    if (($slash_pos = strpos($tag_url, "/")) !== false) {
      $tag_id = substr($tag_url, 0, $slash_pos);
      $tag = ORM::factory("Tag", $tag_id);
      if (!$tag->loaded()) {
        throw HTTP_Exception::factory(404);
      }
      $this->redirect($tag->abs_url(), 301);
    }

    // Find the tag by its canonical URL, which has the form "tag(/<slug>)".
    $tag = ORM::factory("Tag")
      ->where("slug", "=", $tag_url)
      ->find();
    if (!$tag->loaded()) {
      // See if we have a numeric URL, which might be a malformed Gallery 3.0.x URL
      // with the form "tag/<id>" - if so, fire a 301.
      if (!preg_match("/[^0-9]/", $tag_url)) {
        $tag = ORM::factory("Tag", $tag_url);
        if ($tag->loaded()) {
          $this->redirect($tag->abs_url(), 301);
        }
      }
      throw HTTP_Exception::factory(404);
    }

    $root = Item::root();
    $template = new View_Theme("required/page.html", "collection", "tag");
    $template->set_global(array(
      "tag" => $tag,
      "collection_query_callback" => array("Controller_Tags::get_tag_query",   array($tag)),
      "breadcrumbs_callback"      => array("Controller_Tags::get_breadcrumbs", array($tag)),
    ));
    $template->init_collection();

    $template->content = new View("required/dynamic.html");
    $template->content->title = t("Tag: %tag_name", array("tag_name" => $tag->name));

    $this->response->body($template);
  }

  /**
   * Add a tag to an item.  This generates the form, validates it, adds the tag, and returns a
   * response.  This can be used as an ajax form (preferable) or a normal view.
   */
  public function action_add() {
    $item_id = $this->request->arg(0, "digit");
    $item = ORM::factory("Item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    // Build our form.
    $form = Formo::form()
      ->attr("id", "g-add-tag-form")
      ->add_class("g-short-form")
      ->add("tag", "group");
    $form->tag
      ->set("label", t("Add Tag"))
      ->add("name", "input")
      ->add("submit", "input|submit", t("Add Tag"));
    $form->tag->name
      ->set("label", Arr::get(array(
          "album" => t("Add tag to album"),
          "photo" => t("Add tag to photo"),
          "movie" => t("Add tag to movie")
        ), $item->type))
      ->add_rule("not_empty")
      ->add_rule("max_length", array(":value", 128));

    // Get the error messages.
    $form->tag->set_var_fields("error_messages", Controller_Admin_Tags::get_form_error_messages());

    // If sent, validate and create the tag.
    if ($form->load()->validate()) {
      foreach (explode(",", $form->tag->name->val()) as $tag_name) {
        $tag_name = trim($tag_name);
        if ($tag_name) {
          $tag = Tag::add($item, $tag_name);
        }
      }

      $form->set("response", array(
        "cloud" => (string)Tag::cloud(Module::get_var("tag", "tag_cloud_size", 30))));
    }

    $this->response->ajax_form($form);
  }

  /**
   * Return a list of tag names for autocomplete.
   */
  public function action_autocomplete() {
    $tags = array();
    $tag_parts = explode(",", $this->request->query("term"));
    $tag_part = ltrim(end($tag_parts));
    $tag_list = ORM::factory("Tag")
      ->where("name", "LIKE", Database::escape_for_like($tag_part) . "%")
      ->order_by("name", "ASC")
      ->limit(100)
      ->find_all();
    foreach ($tag_list as $tag) {
      $tags[] = (string)HTML::clean($tag->name);
    }

    $this->response->ajax(json_encode($tags));
  }

  /**
   * Find a tag by its name.  This is used in the "tag_name" route, which allows URLs like
   * "tag_name/<tag_name>".  This is deprecated in Gallery 3.1, but we keep it here for
   * backward compatibility.  If found, this will fire a 301 redirect to the canonical URL.
   */
  public function action_find_by_name() {
    $tag_name = $this->request->arg(0);
    $tag = ORM::factory("Tag")->where("name", "=", $tag_name)->find();
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }
    $this->redirect($tag->abs_url(), 301);
  }

  /**
   * Get the tag query for its collection view.
   * @see  Controller_Tags::action_show()
   */
  static function get_tag_query($tag) {
    return $tag->items->viewable();
  }

  /**
   * Get the breadcrumbs for a tag.
   * @see  Controller_Tags::action_show()
   */
  static function get_breadcrumbs($item=null, $tag) {
    $params = $item ? "show={$item->id}" : null;

    $breadcrumbs = Breadcrumb::array_from_item_parents(Item::root());
    $breadcrumbs[] = Breadcrumb::factory(t("Tag: %tag_name", array("tag_name" => $tag->name)),
                                         $tag->url($params));
    if ($item) {
      $breadcrumbs[] = Breadcrumb::factory($item->title, $item->url());
    }

    return $breadcrumbs;
  }
}
