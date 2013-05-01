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
return array(
  // File used for label messages (false or name of message file (ex: "label_messages"))
  "label_message_file"      => false,
  // File used for validation messages (false or name of message file (ex: "validation_messages"))
  "validation_message_file" => false,
  // Whether to translate labels and error messages (false since Gallery has its own I18n system)
  "translate"               => false,
  // Close single html tags (true = <br/>. false = <br>)
  "close_single_html_tags"  => true,
  // Auto-generate IDs on form elements
  "auto_id"                 => false,
  // The directory for the formo templates (ex: "formo" or "formo_bootstrap")
  // @todo: pick one, then delete the other views subdir
  "template_dir"            => "formo/",
  // The extension for the formo templates
  // false or extension (ex: false looks for "template.php", "html" looks for "template.html.php")
  "template_ext"            => false,
  // Namespace fields (name="parent_alias[field_alias]")
  "namespaces"              => true,
  // Driver used for ORM integration
  "orm_driver"              => "kohana",
  // Automatically add these rules to "input" fields for html5 compatability.
  // These are applied to a Validation object, whose rules are in Kohana's Valid class.
  "input_rules" => array(
    "email"          => array(array("email")),
    "tel"            => array(array("phone")),
    "url"            => array(array("url")),
    "date"           => array(array("date")),
    "datetime"       => array(array("date")),
    "datetime-local" => array(array("date")),
    "color"          => array(array("color")),
    "week"           => array(array("regex", array(":value", "/^\d{4}-[Ww](?:0[1-9]|[1-4][0-9]|5[0-2])$/"))),
    "time"           => array(array("regex", array(":value", "/^(?:([0-1]?[0-9])|([2][0-3])):(?:[0-5]?[0-9])(?::([0-5]?[0-9]))?$/"))),
    "month"          => array(array("regex", array(":value", "/^\d{4}-(?:0[1-9]|1[0-2])$/"))),
    "range"          => array(
      array("digit"),
      array("Formo_Validator::range", array(":field", ":form")),
    ),
    "number"        => array(
      array("digit"),
      array("Formo_Validator::range", array(":field", ":form")),
    ),
  ),
);
