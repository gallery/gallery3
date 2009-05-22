<?php defined("SYSPATH") or die("No direct script access.");
/**
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
      foreach (split("[\,\ \;]", $form->add_tag->inputs["name"]->value) as $tag_name) {
        $tag_name = trim($tag_name);
        if ($tag_name) {
          $tag = tag::add($item, $tag_name);
        }
      }

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

  public function organize() {
    access::verify_csrf();

    $itemids = explode("|", Input::instance()->post("item"));
    $form = tag::get_organize_form($itemids);
    $old_tags = $form->tags->value;
    if ($form->validate()) {

      $old_tags = preg_split("/[;,\s]+/", $old_tags);
      sort($old_tags);
      $new_tags = preg_split("/[;,\s]+/", $form->tags->value);
      sort($new_tags);

      $HIGH_VALUE_STRING = "\256";
      for ($old_index = $new_index = 0;;) {
        $old_tag = $old_index >= count($old_tags) ? $HIGH_VALUE_STRING : $old_tags[$old_index];
        $new_tag = $new_index >= count($new_tags) ? $HIGH_VALUE_STRING : $new_tags[$new_index];
        if ($old_tag == $HIGH_VALUE_STRING && $new_tag == $HIGH_VALUE_STRING) {
          break;
        }
        $matches = array();
        $old_star = false;
        if (preg_match("/(.*)(\*)$/", $old_tag, $matches)) {
          $old_star = true;
          $old_tag = $matches[1];
        }
        $new_star = false;
        if (preg_match("/(.*)(\*)$/", $new_tag, $matches)) {
          $new_star = true;
          $new_tag = $matches[1];
        }
        if ($old_tag > $new_tag) {
          // Its missing in the old list so add it
          $this->_add_tag($new_tag, $itemids);
          $new_index++;
        } else if ($old_tag < $new_tag) {
          // Its missing in the new list so its been removed
          $this->_delete_tag($old_tag, $itemids);
          $old_index++;
        } else {
          if ($old_star && !$new_star) {
            // User wants tag to apply to all items, originally only on some of selected
            $this->_update_tag($old_tag, $itemids);
         } // Not changed ignore
          $old_index++;
          $new_index++;
        }
      }
    }
    print json_encode(array("form" => $form->__toString(), "message" => t("Tags updated")));
  }

  public function reset_organize() {
    $itemids = Input::instance()->get("item");

    print tag::get_organize_form($itemids);
  }

  private function _add_tag($new_tag, $itemids) {
    $tag = ORM::factory("tag")
      ->where("name", $new_tag)
      ->find();
    if ($tag->loaded) {
      $tag->count += count($itemids);
    } else {
      $tag->name = $new_tag;
      $tag->count = count($itemids);
    }
    $tag->save();

    $db = Database::instance();
    foreach ($itemids as $item_id) {
      $db->query("INSERT INTO {items_tags} SET item_id = $item_id, tag_id = {$tag->id};");
    }
  }

  private function _delete_tag($new_tag, $itemids) {
    $tag = ORM::factory("tag")
      ->where("name", $new_tag)
      ->find();
    $tag->count -= count($itemids);
    if ($tag->count > 0) {
      $tag->save();
    } else {
      $tag->delete();
    }

    $ids = implode(", ", $itemids);
    Database::instance()->query(
      "DELETE FROM {items_tags} WHERE tag_id = {$tag->id} AND item_id IN ($ids);");
  }

  private function _update_tag($new_tag, $itemids) {
    $tag = ORM::factory("tag")
      ->where("name", $new_tag)
      ->find();

    $db = Database::instance();
    $ids = implode(", ", $itemids);
    $result = $db->query(
      "SELECT item_id FROM {items_tags}
        WHERE tag_id = {$tag->id}
          AND item_id IN ($ids)");

    $add_items = array_fill_keys($itemids, 1);
    foreach($result as $row) {
      unset($add_items[$row->item_id]);
    }
    $add_items = array_keys($add_items);
    $tag->count += count($add_items);
    $tag->save();
    foreach ($add_items as $item_id) {
      $db->query("INSERT INTO {items_tags} SET item_id = $item_id, tag_id = {$tag->id};");
    }
  }
}
