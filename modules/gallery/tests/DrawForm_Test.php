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
class DrawForm_Test extends Unit_Test_Case {
  function no_group_test() {
    $form = new Forge("test/controller", "", "post", array("id" => "gTestGroupForm"));
    $form->input("title")->label(t("Title"));
    $form->textarea("description")->label(t("Text Area"));
    $form->submit("")->value(t("Submit"));
    $rendered = $form->__toString();

    $expected = "<form action=\"http://./index.php/test/controller\" method=\"post\" " .
                      "id=\"gTestGroupForm\">\n" .
                "<input type=\"hidden\" name=\"csrf\" value=\"" . access::csrf_token() . "\"  />\n" .
                "  <ul>\n" .
                "  <li>\n" .
                "    <label for=\"title\" >Title</label>\n" .
                "    <input type=\"text\" id=\"title\" name=\"title\" value=\"\" " .
                            "class=\"textbox\"  />\n" .
                "  </li>\n" .
                "  <li>\n" .
                "    <label for=\"description\" >Text Area</label>\n" .
                "    <textarea id=\"description\" name=\"description\" " .
                              "class=\"textarea\" ></textarea>\n" .
                "  </li>\n" .
                "  <li>\n" .
                "    <input type=\"submit\" value=\"Submit\" class=\"submit\"  />\n" .
                "  </li>\n" .
                "  </ul>\n" .
                "</form>\n";
    $this->assert_same($expected, $rendered);
  }

  function group_test() {
    $form = new Forge("test/controller", "", "post", array("id" => "gTestGroupForm"));
    $group = $form->group("test_group")->label(t("Test Group"));
    $group->input("title")->label(t("Title"));
    $group->textarea("description")->label(t("Text Area"));
    $group->submit("")->value(t("Submit"));
    $rendered = $form->__toString();

    $expected = "<form action=\"http://./index.php/test/controller\" method=\"post\" " .
                      "id=\"gTestGroupForm\">\n" .
                "<input type=\"hidden\" name=\"csrf\" value=\"" . access::csrf_token() . "\"  />\n" .
                "  <fieldset>\n" .
                "    <legend>Test Group</legend>\n" .
                "    <ul>\n" .
                "      <li>\n" .
                "        <label for=\"title\" >Title</label>\n" .
                "        <input type=\"text\" id=\"title\" name=\"title\" value=\"\" " .
                            "class=\"textbox\"  />\n" .
                "      </li>\n" .
                "      <li>\n" .
                "        <label for=\"description\" >Text Area</label>\n" .
                "        <textarea id=\"description\" name=\"description\" " .
                              "class=\"textarea\" ></textarea>\n" .
                "      </li>\n" .
                "      <li>\n" .
                "        <input type=\"submit\" value=\"Submit\" class=\"submit\"  />\n" .
                "      </li>\n" .
                "    </ul>\n" .
                "  </fieldset>\n" .
                "</form>\n";
    $this->assert_same($expected, $rendered);
  }

}

