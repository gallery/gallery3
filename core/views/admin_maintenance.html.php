<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gMaintenance">
  <h1> <?= _("Maintenance Tasks") ?> </h1>
  <p>
    <?= _("Occasionally your Gallery will require some maintenance.  Here are some tasks you can use to keep it running smoothly.") ?>
  </p>

  <div id="gAvailableTasks">
    <h2> <?= _("Available Tasks") ?> </h2>
    <table style="width: 680px" border="1">
      <tr>
        <th>
          <?= _("Name") ?>
        </th>
        <th>
          <?= _("Description") ?>
        </th>
        <th>
          <?= _("Action") ?>
        </th>
      </tr>
      <? foreach ($task_definitions as $task) ?>
      <tr class="<?= log::severity_class($task->severity) ?>">
        <td>
          <?= $task->name ?>
        </td>
        <td>
          <?= $task->description ?>
        </td>
        <td>
          <a href="<?= url::site("admin/maintenance/start/$task->callback?csrf=$csrf") ?>"
            class="gDialogLink">
            <?= _("run") ?>
          </a>
        </td>
      </tr>
    </table>
  </div>

  <div id="gRunningTasks">
    <h2> <?= _("Running Tasks") ?> </h2>

    <table style="width: 680px" border="1">
      <tr>
        <th>
          <?= _("Last Updated") ?>
        </th>
        <th>
          <?= _("Name") ?>
        </th>
        <th>
          <?= _("Status") ?>
        </th>
        <th>
          <?= _("Info") ?>
        </th>
        <th>
          <?= _("Action") ?>
        </th>
      </tr>
      <? foreach ($running_tasks as $task): ?>
      <tr class="<?= $task->state == "stalled" ? "gWarning" : "" ?>">
        <td>
          <?= date("M j, Y H:i:s", $task->updated) ?>
        </td>
        <td>
          <?= $task->name ?>
        </td>
        <td>
          <? if ($task->done): ?>
          <? if ($task->state == "cancelled"): ?>
          <?= _("Cancelled") ?>
          <? endif ?>
          <?= _("Done") ?>
          <? elseif ($task->state == "stalled"): ?>
          <?= _("Stalled") ?>
          <? else: ?>
          <?= sprintf(_("%d%% Complete"), $task->percent_complete) ?>
          <? endif ?>
        </td>
        <td>
          <?= $task->status ?>
        </td>
        <td>
          <? if ($task->state == "stalled"): ?>
          <a href="<?= url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>" class="gDialogLink">
            <?= _("resume") ?>
          </a>
          <? endif ?>
          <a href="<?= url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>">
            <?= _("cancel") ?>
          </a>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>

  <div id="gFinishedTasks">
    <h2> <?= _("Finished Tasks") ?> </h2>

    <table style="width: 680px" border="1">
      <tr>
        <th>
          <?= _("Last Updated") ?>
        </th>
        <th>
          <?= _("Name") ?>
        </th>
        <th>
          <?= _("Status") ?>
        </th>
        <th>
          <?= _("Info") ?>
        </th>
        <th>
          <?= _("Action") ?>
        </th>
      </tr>
      <? foreach ($finished_tasks as $task): ?>
      <tr class="<?= $task->state == "success" ? "gSuccess" : "gError" ?>">
        <td>
          <?= date("M j, Y H:i:s", $task->updated) ?>
        </td>
        <td>
          <?= $task->name ?>
        </td>
        <td>
          <? if ($task->state == "success"): ?>
          <?= _("Success") ?>
          <? elseif ($task->state == "error"): ?>
          <?= _("Failed") ?>
          <? elseif ($task->state == "cancelled"): ?>
          <?= _("Cancelled") ?>
          <? endif ?>
        </td>
        <td>
          <?= $task->status ?>
        </td>
        <td>
          <? if ($task->done): ?>
          <a href="<?= url::site("admin/maintenance/remove/$task->id?csrf=$csrf") ?>">
            <?= _("remove") ?>
          </a>
          <? else: ?>
          <a href="<?= url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>">
            <?= _("resume") ?>
          </a>
          <a href="<?= url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>">
            <?= _("cancel") ?>
          </a>
          <? endif ?>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>
</div>
