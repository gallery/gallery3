<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-admin-maintenance" class="g-block">
  <h1> <?= t("Maintenance") ?> </h1>
  <div class="g-block-content">
    <div id="g-maintenance-mode">
      <p>
      <?= t("When you're performing maintenance on your Gallery, you can enable <b>maintenance mode</b> which prevents any non-admin from accessing your Gallery.  Some of the tasks below will automatically put your Gallery in maintenance mode for you.") ?>
      </p>
      <ul id="g-action-status" class="g-message-block">
        <?php if (module::get_var("gallery", "maintenance_mode")): ?>
        <li class="g-warning">
          <?= t("Maintenance mode is <b>on</b>.  Non admins cannot access your Gallery.  <a href=\"%enable_maintenance_mode_url\">Turn off maintenance mode</a>", array("enable_maintenance_mode_url" => url::site("admin/maintenance/maintenance_mode/0?csrf=$csrf"))) ?>
        </li>
        <?php else: ?>
        <li class="g-info">
          <?= t("Maintenance mode is off.  User access is permitted.  <a href=\"%enable_maintenance_mode_url\">Turn on maintenance mode</a>", array("enable_maintenance_mode_url" => url::site("admin/maintenance/maintenance_mode/1?csrf=$csrf"))) ?>
        </li>
        <?php endif ?>
      </ul>
    </div>
  </div>

  <div class="g-block-content">
    <div id="g-available-tasks">
      <h2> <?= t("Maintenance tasks") ?> </h2>
      <p>
        <?= t("Occasionally your Gallery will require some maintenance.  Here are some tasks you can use to keep it running smoothly.") ?>
      </p>
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
        <?php foreach ($task_definitions as $task): ?>
        <tr class="<?= text::alternate("g-odd", "g-even") ?> <?= log::severity_class($task->severity) ?>">
          <td class="<?= log::severity_class($task->severity) ?>">
            <?= $task->name ?>
          </td>
          <td>
            <?= $task->description ?>
          </td>
          <td>
            <a href="<?= url::site("admin/maintenance/start/$task->callback?csrf=$csrf") ?>"
              class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all">
              <?= t("run") ?>
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </table>
    </div>

    <?php if ($running_tasks->count()): ?>
    <div id="g-running-tasks">
      <a href="<?= url::site("admin/maintenance/cancel_running_tasks?csrf=$csrf") ?>"
         class="g-button g-right ui-icon-left ui-state-default ui-corner-all">
        <?= t("cancel all running") ?></a>
      <h2> <?= t("Running tasks") ?> </h2>
      <table>
        <tr>
          <th>
            <?= t("Last updated") ?>
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
        <?php foreach ($running_tasks as $task): ?>
        <tr class="<?= text::alternate("g-odd", "g-even") ?> <?= $task->state == "stalled" ? "g-warning" : "" ?>">
          <td class="<?= $task->state == "stalled" ? "g-warning" : "" ?>">
            <?= gallery::date_time($task->updated) ?>
          </td>
          <td>
            <?= $task->name ?>
          </td>
          <td>
            <?php if ($task->done): ?>
            <?php if ($task->state == "cancelled"): ?>
            <?= t("Cancelled") ?>
            <?php endif ?>
            <?= t("Close") ?>
            <?php elseif ($task->state == "stalled"): ?>
            <?= t("Stalled") ?>
            <?php else: ?>
            <?= t("%percent_complete% Complete", array("percent_complete" => $task->percent_complete)) ?>
            <?php endif ?>
          </td>
          <td>
            <?= $task->status ?>
          </td>
          <td>
            <?= html::clean($task->owner()->name) ?>
          </td>
          <td>
            <?php if ($task->state == "stalled"): ?>
            <a class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"
               href="<?= url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>">
              <?= t("resume") ?>
            </a>
            <?php endif ?>
            <?php if ($task->get_log()): ?>
            <a href="<?= url::site("admin/maintenance/show_log/$task->id?csrf=$csrf") ?>" class="g-dialog-link g-button ui-state-default ui-corner-all">
              <?= t("view log") ?>
            </a>
            <?php endif ?>
            <a href="<?= url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>"
               class="g-button ui-icon-left ui-state-default ui-corner-all">
              <?= t("cancel") ?>
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </table>
    </div>
    <?php endif ?>

    <?php if ($finished_tasks->count()): ?>
    <div id="g-finished-tasks">
      <a href="<?= url::site("admin/maintenance/remove_finished_tasks?csrf=$csrf") ?>"
           class="g-button g-right ui-icon-left ui-state-default ui-corner-all">
         <span class="ui-icon ui-icon-trash"></span><?= t("remove all finished") ?></a>
      <h2> <?= t("Finished tasks") ?> </h2>
      <table>
        <tr>
          <th>
            <?= t("Last updated") ?>
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
        <?php foreach ($finished_tasks as $task): ?>
        <tr class="<?= text::alternate("g-odd", "g-even") ?> <?= $task->state == "success" ? "g-success" : "g-error" ?>">
          <td class="<?= $task->state == "success" ? "g-success" : "g-error" ?>">
            <?= gallery::date_time($task->updated) ?>
          </td>
          <td>
            <?= $task->name ?>
          </td>
          <td>
            <?php if ($task->state == "success"): ?>
            <?= t("Success") ?>
            <?php elseif ($task->state == "error"): ?>
            <?= t("Failed") ?>
            <?php elseif ($task->state == "cancelled"): ?>
            <?= t("Cancelled") ?>
            <?php endif ?>
          </td>
          <td>
            <?= $task->status ?>
          </td>
          <td>
            <?= html::clean($task->owner()->name) ?>
          </td>
          <td>
            <?php if ($task->done): ?>
            <a href="<?= url::site("admin/maintenance/remove/$task->id?csrf=$csrf") ?>" class="g-button ui-state-default ui-corner-all">
              <?= t("remove") ?>
            </a>
            <?php if ($task->get_log()): ?>
            <a href="<?= url::site("admin/maintenance/show_log/$task->id?csrf=$csrf") ?>" class="g-dialog-link g-button ui-state-default ui-corner-all">
              <?= t("view log") ?>
            </a>
            <?php endif ?>
            <?php else: ?>
            <a href="<?= url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>" class="g-dialog-link g-button" ui-state-default ui-corner-all>
              <?= t("resume") ?>
            </a>
            <a href="<?= url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>" class="g-button ui-state-default ui-corner-all">
              <?= t("cancel") ?>
            </a>
            <?php endif ?>
            </ul>
          </td>
        </tr>
        <?php endforeach ?>
      </table>
    </div>
    <?php endif ?>
  </div>
</div>
