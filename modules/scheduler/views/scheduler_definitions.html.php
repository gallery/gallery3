<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-sheduled-tasks">
  <h2> <?= t("Scheduled tasks") ?> </h2>
  <table>
    <tr>
      <th>
        <?= t("Name") ?>
      </th>
      <th>
        <?= t("Next run") ?>
      </th>
      <th>
        <?= t("Interval") ?>
      </th>
      <th>
        <?= t("Status") ?>
      </th>
      <th>
        <?= t("Action") ?>
      </th>
    </tr>
    <? foreach ($schedule_definitions as $entry): ?>
    <tr class="<?= text::alternate("g-odd", "g-even") ?>">
      <td>
        <?= html::clean($entry->name) ?>
      </td>
      <td>
        <?= html::clean($entry->run_date) ?>
      </td>
      <td>
        <?= html::clean($entry->interval) ?>
      </td>
      <td>
        <?= html::clean($entry->status) ?>
      </td>
      <td>
        <a href="<?= url::site("admin/schedule/update_form/$entry->id?csrf=$csrf") ?>"
           class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all">
          <?= t("edit") ?>
        </a>
        <a href="<?= url::site("admin/schedule/remove_form/$entry->id?csrf=$csrf") ?>"
           class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all">
          <?= t("remove") ?>
        </a>
      </td>
    </tr>
    <? endforeach ?>
  </table>
</div>
