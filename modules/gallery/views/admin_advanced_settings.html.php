<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-admin-advanced-settings" class="g-block">
  <h1> <?= t("Advanced settings") ?> </h1>
  <p>
    <?= t("Here are internal Gallery configuration settings.  Most of these settings are accessible elsewhere in the administrative console.") ?>
  </p>

  <ul id="g-action-status" class="g-message-block">
    <li class="g-warning"><?= t("Change these values at your own risk!") ?></li>
  </ul>

  <?= t("Filter:") ?> <input id="g-admin-advanced-settings-filter" type="text"></input>
  <div class="g-block-content">
    <table>
      <tr>
        <th> <?= t("Module") ?> </th>
        <th> <?= t("Name") ?> </th>
        <th> <?= t("Value") ?></th>
      </tr>
      <?php foreach ($vars as $var): ?>
      <tr class="setting-row <?= text::alternate("g-odd", "g-even") ?>">
        <td> <?= html::clean($var->module_name) ?> </td>
        <td> <?= html::clean($var->name) ?> </td>
        <td>
          <a href="<?= url::site("admin/advanced_settings/edit/$var->module_name/" . html::clean($var->name)) ?>"
            class="g-dialog-link"
            title="<?= t("Edit %var (%module_name)", array("var" => $var->name, "module_name" => $var->module_name))->for_html_attr() ?>">
            <?php if (!isset($var->value) || $var->value === ""): ?>
            <i> <?= t("empty") ?> </i>
            <?php else: ?>
            <?= html::clean($var->value) ?>
            <?php endif ?>
        </a>
        </td>
      </tr>
      <?php endforeach ?>
    </table>
  </div>

  <script>
    $(document).ready(function() {
      $("#g-admin-advanced-settings-filter").on("input keyup", function() {
        var filter = $(this).val();
        if (filter) {
          $("tr.setting-row").fadeOut("fast");
          $("tr.setting-row").each(function() {
            if ($(this).text().indexOf(filter) > 0) {
              $(this).stop().show();
            }
          });
        } else {
          $("tr.setting-row").show();
        }
      });
    });
  </script>
</div>
