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
class DrawForm_Test extends Gallery_Unit_Test_Case {
  function no_group_test() {
    $form = new Forge("test/controller", "", "post", array("id" => "g-test-group-form"));
    $form->input("title")->label(t("Title"));
    $form->textarea("description")->label(t("Text Area"));
    $form->submit("")->value(t("Submit"));

    $csrf = access::csrf_token();
    $expected = "<form action=\"http://./index.php/test/controller\" method=\"post\" " .
                      "id=\"g-test-group-form\">\n" .
                "<input type=\"hidden\" name=\"csrf\" value=\"$csrf\"  />" .
                "  <ul>\n" .
                "  <li>\n" .
                "    <label for=\"title\" >Title</label>\n" .
                "    <input type=\"text\" name=\"title\" value=\"\" " .
                            "class=\"textbox\"  />\n" .
                "  </li>\n" .
                "  <li>\n" .
                "    <label for=\"description\" >Text Area</label>\n" .
                "    <textarea name=\"description\" rows=\"\" cols=\"\" " .
                              "class=\"textarea\" ></textarea>\n" .
                "  </li>\n" .
                "  <li>\n" .
                "    <input type=\"submit\" value=\"Submit\" class=\"submit\"  />\n" .
                "  </li>\n" .
                "  </ul>\n" .
                "</form>";
    $this->assert_same($expected, (string) $form);
  }

  function group_test() {
    $form = new Forge("test/controller", "", "post", array("id" => "g-test-group-form"));
    $group = $form->group("test_group")->label(t("Test Group"));
    $group->input("title")->label(t("Title"));
    $group->textarea("description")->label(t("Text Area"));
    $group->submit("")->value(t("Submit"));

    $csrf = access::csrf_token();
    $expected = "<form action=\"http://./index.php/test/controller\" method=\"post\" " .
                      "id=\"g-test-group-form\">\n" .
                "<input type=\"hidden\" name=\"csrf\" value=\"$csrf\"  />" .
                "  <fieldset>\n" .
                "    <legend>Test Group</legend>\n" .
                "    <ul>\n" .
                "      <li>\n" .
                "        <label for=\"title\" >Title</label>\n" .
                "        <input type=\"text\" name=\"title\" value=\"\" " .
                            "class=\"textbox\"  />\n" .
                "      </li>\n" .
                "      <li>\n" .
                "        <label for=\"description\" >Text Area</label>\n" .
                "        <textarea name=\"description\" rows=\"\" cols=\"\" " .
                              "class=\"textarea\" ></textarea>\n" .
                "      </li>\n" .
                "      <li>\n" .
                "        <input type=\"submit\" value=\"Submit\" class=\"submit\"  />\n" .
                "      </li>\n" .
                "    </ul>\n" .
                "  </fieldset>\n" .
                "</form>";
    $this->assert_same($expected, (string) $form);
  }

  function form_script_test() {
    $form = new Forge("test/controller", "", "post", array("id" => "g-test-group-form"));
    $group = $form->group("test_group")->label(t("Test Group"));
    $group->input("title")->label(t("Title"));
    $group->textarea("description")->label(t("Text Area"));
    $form->script("")
      ->url(url::file("test.js"))
      ->text("alert('Test Javascript');");
    $group->submit("")->value(t("Submit"));

    $csrf = access::csrf_token();
    $expected = "<form action=\"http://./index.php/test/controller\" method=\"post\" " .
                      "id=\"g-test-group-form\">\n" .
                "<input type=\"hidden\" name=\"csrf\" value=\"$csrf\"  />" .
                "  <fieldset>\n" .
                "    <legend>Test Group</legend>\n" .
                "    <ul>\n" .
                "      <li>\n" .
                "        <label for=\"title\" >Title</label>\n" .
                "        <input type=\"text\" name=\"title\" value=\"\" " .
                            "class=\"textbox\"  />\n" .
                "      </li>\n" .
                "      <li>\n" .
                "        <label for=\"description\" >Text Area</label>\n" .
                "        <textarea name=\"description\" rows=\"\" cols=\"\" " .
                              "class=\"textarea\" ></textarea>\n" .
                "      </li>\n" .
                "      <li>\n" .
                "        <input type=\"submit\" value=\"Submit\" class=\"submit\"  />\n" .
                "      </li>\n" .
                "    </ul>\n" .
                "  </fieldset>\n" .
                "<script type=\"text/javascript\" src=\"http://./test.js\"></script>\n\n" .
                "<script type=\"text/javascript\">\n" .
                "alert('Test Javascript');\n" .
                "</script>\n" .
                "</form>";
    $this->assert_same($expected, (string) $form);
  }

  function two_hiddens_test() {
    $form = new Forge("test/controller", "", "post");
    $form->hidden("HIDDEN_NAME")->value("HIDDEN_VALUE");

    $csrf = access::csrf_token();
    $expected = "<form action=\"http://./index.php/test/controller\" method=\"post\" class=\"form\">\n" .
                "<input type=\"hidden\" name=\"csrf\" value=\"$csrf\"  />" .
                "<input type=\"hidden\" name=\"HIDDEN_NAME\" value=\"HIDDEN_VALUE\"  />" .
                "  <ul>\n" .
                "  </ul>\n" .
                "</form>";
    $this->assert_same($expected, (string) $form);
  }
}

