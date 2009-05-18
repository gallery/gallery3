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
class tag_event_Core {
  /**
   * Handle the creation of a new photo.
   * @todo Get tags from the XMP and/or IPTC data in the image
   *
   * @param Item_Model $photo
   */
  static function item_created($photo) {
    $tags = array();
    if ($photo->is_photo()) {
      $path = $photo->file_path();
      $size = getimagesize($photo->file_path(), $info);
      if (is_array($info) && !empty($info["APP13"])) {
        $iptc = iptcparse($info["APP13"]);
        if (!empty($iptc["2#025"])) {
          foreach($iptc["2#025"] as $tag) {
            $tag = str_replace("\0",  "", $tag);
            if (function_exists("mb_detect_encoding") && mb_detect_encoding($tag) != "UTF-8") {
              $tag = utf8_encode($tag);
            }
            $tags[$tag] = 1;
          }
        }
      }
    }

    // @todo figure out how to read the keywords from xmp
    foreach(array_keys($tags) as $tag) {
      tag::add($photo, $tag);
    }

    return;
  }

  static function item_before_delete($item) {
    $db = Database::instance();
    $db->query("UPDATE {tags} SET `count` = `count` - 1 WHERE `count` > 0 " .
               "AND `id` IN (SELECT `tag_id` from {items_tags} WHERE `item_id` = $item->id)");
    $db->query("DELETE FROM {tags} WHERE `count` = 0 AND `id` IN (" .
               "SELECT `tag_id` from {items_tags} WHERE `item_id` = $item->id)");
    $db->delete("items_tags", array("item_id" => "$item->id"));
  }

  static function organize_form_creation($event_parms) {
    $v = new View("tag_organize.html");
    $v->tags = array();

    $ids = implode(", ", $event_parms->itemids);
    $db = Database::instance();
    $tags = $db->query("SELECT it.tag_id, t.name,
                               COUNT(DISTINCT it.item_id) as item_count,
                               UPPER(SUBSTR(t.name, 1, 1)) as first_letter
                          FROM {items_tags} it, {tags} t
                         WHERE it.tag_id = t.id
                           AND it.item_id in($ids)
                        GROUP BY it.tag_id
                        ORDER BY first_letter ASC, t.name ASC");
    foreach ($tags as $tag) {
      $v->tags[$tag->first_letter]["taglist"][] =
        array("id" => $tag->tag_id, "tag" => $tag->name, "count" => $tag->item_count);
    }
    $v->tag_count = $tags->count();

    $letters = $db->query("SELECT COUNT(DISTINCT it.item_id) as letter_count,
                               UPPER(SUBSTR(t.name, 1, 1)) as first_letter
                          FROM {items_tags} it, {tags} t
                         WHERE it.tag_id = t.id
                           AND it.item_id in($ids)
                        GROUP BY first_letter
                        ORDER BY first_letter ASC");
    foreach ($letters as $letter) {
      $v->tags[$letter->first_letter]["count"] = $letter->letter_count;
    }

    $event_parms->panes[] = array("label" => t("Manage Tags"), "content" => $v);
  }
}
