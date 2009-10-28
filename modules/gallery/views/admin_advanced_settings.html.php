<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-admin-advanced-settings" class="g-block">
  <h1> <?= t("Advanced settings") ?> </h1>
  <p>
    <?= t("Here are internal Gallery configuration settings.  Most of these settings are accessible elsewhere in the administrative console.") ?>
  </p>

  <ul id="g-action-status" class="g-message-block">
    <li class="g-warning"><?= t("Change these values at your own risk!") ?></li>
  </ul>

  <div class="g-block-content">
    <table>
      <tr>
        <th> <?= t("Module") ?> </th>
        <th> <?= t("Name") ?> </th>
        <th> <?= t("Value") ?></th>
      </tr>
      <? $i = 0; ?>
      <? foreach ($vars as $var): ?>
      <? if ($var->module_name == "gallery" && $var->name == "_cache") continue ?>
      <tr class="<?= ($i % 2 == 0) ? "g-odd" : "g-even" ?>">
        <td> <?= $var->module_name ?> </td>
        <td> <?= html::clean($var->name) ?> </td>
        <td>
          <a href="<?= url::site("admin/advanced_settings/edit/$var->module_name/" . html::clean($var->name)) ?>"
            class="g-dialog-link"
            title="<?= t("Edit %var (%module_name)", array("var" => $var->name, "module_name" => $var->module_name))->for_html_attr() ?>">
            <? if ($var->value): ?>
            <?= html::clean($var->value) ?>
            <? else: ?>
            <i> <?= t("empty") ?> </i>
            <? endif ?>
        </a>
        </td>
      </tr>
      <? $i++ ?>
      <? endforeach ?>
    </table>
  </div>
</div>
