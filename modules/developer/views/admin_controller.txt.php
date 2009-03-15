<?php defined("SYSPATH") or die("No direct script access."); ?>
<?= "<?php defined(\"SYSPATH\") or die(\"No direct script access.\");" ?>
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Admin_<?= $class_name ?>_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_<?=$module ?>.html");
    $view->content->form = $this->_get_admin_form();

    print $view;
  }

  public function handler() {
    access::verify_csrf();

    $form = $this->_get_admin_form();
    if ($form->validate()) {
      // @todo process the admin form

      message::success(t("<?= $name ?> Adminstration Complete Successfully"));

      url::redirect("admin/<?= $module ?>");
    }
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_<?=$module ?>.html");
    $view->content->form = $form;

    print $view;
  }

  private function _get_admin_view($form=null) {
  }
  
  private function _get_admin_form() {
    $form = new Forge("admin/<?= $module ?>/handler", "", "post",
                      array("id" => "gAdminForm"));
    $group = $form->group("group");
    $group->input("text")->label(t("Text"))->rules("required");
    $group->submit("submit")->value(t("Submit"));

    return $form;
  }
}