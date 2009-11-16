<?php defined("SYSPATH") or die("No direct script access.") ?>
<li>
  <? $name = $menu->label->for_html() ?>
  <? if (identity::is_writable()): ?>
  <?= t("Logged in as %name", array("name" => html::mark_clean(
        "<a href='$menu->url' title='" . t("Edit your profile")->for_html_attr() .
        "' id='$menu->id' class='g-dialog-link'>{$name}</a>"))) ?>
  <? else: ?>
  <?= t("Logged in as %name", array("name" => $name)) ?>
  <? endif ?>
</li>
