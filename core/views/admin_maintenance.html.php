<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminMaintenance">
  <h1> <?= t("Maintenance Tasks") ?> </h1>
  <p>
    <?= t("Occasionally your Gallery will require some maintenance.  Here are some tasks you can use to keep it running smoothly.") ?>
  </p>

  <div id="gAvailableTasks">
    <h2> <?= t("Available Tasks") ?> </h2>
    <table>
      <tr>
        <th>
          <?= t("Name") ?>
        </th>
        <th>
          <?= t("Description") ?>
        </th>
        <th>
          <?= t("Action") ?>
        </th>
      </tr>
      <? foreach ($task_definitions as $task): ?>
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
            <?= t("run") ?>
          </a>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>

  <div id="gRunningTasks">
    <h2> <?= t("Running Tasks") ?> </h2>

    <table>
      <tr>
        <th>
          <?= t("Last Updated") ?>
        </th>
        <th>
          <?= t("Name") ?>
        </th>
        <th>
          <?= t("Status") ?>
        </th>
        <th>
          <?= t("Info") ?>
        </th>
        <th>
          <?= t("Owner") ?>
        </th>
        <th>
          <?= t("Action") ?>
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
          <?= t("Cancelled") ?>
          <? endif ?>
          <?= t("Done") ?>
          <? elseif ($task->state == "stalled"): ?>
          <?= t("Stalled") ?>
          <? else: ?>
          <?= t("%percent_complete% Complete", array("percent_complete" => $task->percent_complete)) ?>
          <? endif ?>
        </td>
        <td>
          <?= $task->status ?>
        </td>
        <td>
          <?= $task->user_name ?>
        </td>
        <td>
          <? if ($task->state == "stalled"): ?>
          <a href="<?= url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>" class="gDialogLink">
            <?= t("resume") ?>
          </a>
          <? endif ?>
          <a href="<?= url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>">
            <?= t("cancel") ?>
          </a>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>

  <div id="gFinishedTasks">
    <h2> <?= t("Finished Tasks") ?> </h2>

    <table>
      <tr>
        <th>
          <?= t("Last Updated") ?>
        </th>
        <th>
          <?= t("Name") ?>
        </th>
        <th>
          <?= t("Status") ?>
        </th>
        <th>
          <?= t("Info") ?>
        </th>
        <th>
          <?= t("Owner") ?>
        </th>
        <th>
          <?= t("Action") ?>
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
          <?= t("Success") ?>
          <? elseif ($task->state == "error"): ?>
          <?= t("Failed") ?>
          <? elseif ($task->state == "cancelled"): ?>
          <?= t("Cancelled") ?>
          <? endif ?>
        </td>
        <td>
          <?= $task->status ?>
        </td>
        <td>
          <?= $task->user_name ?>
        </td>
        <td>
          <? if ($task->done): ?>
          <a href="<?= url::site("admin/maintenance/remove/$task->id?csrf=$csrf") ?>">
            <?= t("remove") ?>
          </a>
          <? else: ?>
          <a href="<?= url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>">
            <?= t("resume") ?>
          </a>
          <a href="<?= url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>">
            <?= t("cancel") ?>
          </a>
          <? endif ?>
        </td>
      </tr>
      <? endforeach ?>
    </table>
  </div>
</div>
