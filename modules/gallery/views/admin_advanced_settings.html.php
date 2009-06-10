<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminAdvancedSettings">
  <h1> <?= t("Advanced Settings") ?> </h1>
  <p>
    <?= t("Here are internal Gallery configuration settings.  Most of these settings are accessible elsewhere in the administrative console.") ?>
  </p>
  <ul id="gMessage">
    <li class="gWarning">
      <b><?= t("Change these values at your own risk!") ?>
    </li>
  </ul>

  <table>
    <tr>
      <th> <?= t("Module") ?> </th>
      <th> <?= t("Name") ?> </th>
      <th> <?= t("Value") ?></th>
    </tr>
    <? foreach ($vars as $var): ?>
    <? if ($var->module_name == "gallery" && $var->name == "_cache") continue ?>
    <tr class="setting">
      <td> <?= $var->module_name ?> </td>
      <td> <?= p::clean($var->name) ?> </td>
      <td>
        <a href="<?= url::site("admin/advanced_settings/edit/$var->module_name/" . p::clean($var->name)) ?>"
          class="gDialogLink"
          title="<?= t("Edit %var (%module_name)", array("var" => p::clean($var->name), "module_name" => $var->module_name)) ?>">
          <? if ($var->value): ?>
          <?= p::clean($var->value) ?>
          <? else: ?>
          <i> <?= t("empty") ?> </i>
          <? endif ?>
      </a>
      </td>
    </tr>
    <? endforeach ?>
  </table>
</div>
