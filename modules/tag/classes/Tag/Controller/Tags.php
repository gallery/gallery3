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
   * Show a tag's items.  This finds the tag(s) by its URL and generates a view.
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

    // Get the route and look for slashes commas and/or slashes.
    $tag_url = $this->request->param("tag_url");
    if (($slash_pos = strpos($tag_url, "/")) !== false) {
      // We have a slash in the URL, which might be a Gallery 3.0.x canonical URL with
      // the form "tag/<id>/<name>" - if so, fire a 301; if not, fire a 404.
      $tag_id = substr($tag_url, 0, $slash_pos);
      $tag = ORM::factory("Tag", $tag_id);
      if (!$tag->loaded()) {
        throw HTTP_Exception::factory(404);
      }
      $this->redirect($tag->abs_url(), 301);
    } else if (empty($tag_url)) {
      // @todo: this is the future home of the album of all tags.  For now, we 404.
      throw HTTP_Exception::factory(404);
    } else {
      // Find the tags by their canonical URLs, which have the form "tag/<slug_1>(,<slug_2>(,...))".
      $slugs = array_unique(array_filter(explode(",", $tag_url)));

      // Check for extra commas and duplicate tags and redirect as needed.
      // (e.g. "tag/,foo,,bar,bar," --> "tag/foo,bar")
      $filtered_url = implode(",", $slugs);
      if ($tag_url !== $filtered_url) {
        $this->redirect("tag/$filtered_url", 301);
      }

      $tags = array();
      for ($i = 0; $i < count($slugs); $i++) {
        $tags[$i] = ORM::factory("Tag", array("slug" => $slugs[$i]));
        if (!$tags[$i]->loaded()) {
          if ($i == 0) {
            // See if we have a numeric URL, which might be a malformed Gallery 3.0.x URL
            // with the form "tag/<id>" - if so, fire a 301; if not, fire a 404.
            if (!preg_match("/[^0-9]/", $slugs[$i])) {
              $tag = ORM::factory("Tag", $slugs[$i]);
              if ($tag->loaded()) {
                $this->redirect($tag->abs_url(), 301);
              }
            }
          }

          throw HTTP_Exception::factory(404);
        }
      }

      $template = new View_Theme("required/page.html", "collection", "tag");
      $template->set_global(array(
        "tag" => $tags[0],  // backward compatibility - @todo: remove the need for this.
        "tags" => $tags,
        "collection_query_callback" => array("Controller_Tags::get_tag_query",   array($tags)),
        "breadcrumbs_callback"      => array("Controller_Tags::get_breadcrumbs", array($tags)),
      ));
      $template->init_collection();

      $template->content = new View("required/dynamic.html");
      $template->content->title = Tag::title($tags);
    }

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
    $tag = ORM::factory("Tag", array("name" => $tag_name));
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }
    $this->redirect($tag->abs_url(), 301);
  }

  /**
   * Get the tag query for its collection view.
   * @see  Controller_Tags::action_show()
   */
  static function get_tag_query($tags) {
    if (count($tags) == 1) {
      return $tags[0]->items->viewable();
    }

    // ORM doesn't handle multiple "has many through" relationships very easily,
    // so we query the pivot table manually.
    // @todo: try to do this in 1 query instead of 2 without tripping up count_all() and find_all().

    $tag_ids = array();
    foreach ($tags as $tag) {
      $tag_ids[] = $tag->id;
    }

    // Find the item ids that are found in the pivot table with *every* tag id.  We do a
    // "where in...", which finds the "OR" set, then require that the count is the total number
    // of tags, effectively making it an "AND" set.
    $rows = DB::select("item_id")
      ->distinct(true)
      ->select(array(DB::expr("COUNT(\"*\")"), "C"))
      ->from("items_tags")
      ->where("tag_id", "IN", $tag_ids)
      ->having("C", "=", count($tags))
      ->group_by("item_id")
      ->as_object()
      ->execute();

    $item_ids = array();
    foreach ($rows as $row) {
      $item_ids[] = $row->item_id;
    }

    // Return the ORM query.
    return ORM::factory("Item")->where("item.id", "IN", $item_ids)->viewable();
  }

  /**
   * Get the breadcrumbs for a tag.
   * @see  Controller_Tags::action_show()
   */
  static function get_breadcrumbs($item=null, $tags) {
    $params = $item ? "show={$item->id}" : null;

    $breadcrumbs = Breadcrumb::array_from_item_parents(Item::root());
    $breadcrumbs[] = Breadcrumb::factory(Tag::title($tags), Tag::url($tags, $params));
    if ($item) {
      $breadcrumbs[] = Breadcrumb::factory($item->title, $item->url());
    }

    return $breadcrumbs;
  }
}
