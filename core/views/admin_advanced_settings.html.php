<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminAdvancedSettings">
  <h1> <?= t("Advanced Settings") ?> </h1>
  <p>
    <?= t("Here are internal Gallery configuration settings.  Most of these settings are accessible elsewhere in the administrative console.  You will eventually be able to modify these directly (at your own risk).") ?>
  </p>
  <table>
    <tr>
      <th> <?= t("Module") ?> </th>
      <th> <?= t("Name") ?> </th>
      <th> <?= t("Value") ?></th>
    </tr>
    <? foreach ($vars as $var): ?>
    <tr class="setting">
      <td> <?= $var->module_name ?> </td>
      <td> <?= $var->name ?> </td>
      <td> <?= $var->value ?> </td>
    </tr>
    <? endforeach ?>
  </table>
</div>
