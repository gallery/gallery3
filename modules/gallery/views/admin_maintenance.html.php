<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-admin-maintenance" class="g-block">
  <h1> <?php echo  t("Maintenance") ?> </h1>
  <div class="g-block-content">
    <div id="g-maintenance-mode">
      <p>
      <?php echo  t("When you're performing maintenance on your Gallery, you can enable <b>maintenance mode</b> which prevents any non-admin from accessing your Gallery.  Some of the tasks below will automatically put your Gallery in maintenance mode for you.") ?>
      </p>
      <ul id="g-action-status" class="g-message-block">
        <?php if (module::get_var("gallery", "maintenance_mode")): ?>
        <li class="g-warning">
          <?php echo  t("Maintenance mode is <b>on</b>.  Non admins cannot access your Gallery.  <a href=\"%enable_maintenance_mode_url\">Turn off maintenance mode</a>", array("enable_maintenance_mode_url" => url::site("admin/maintenance/maintenance_mode/0?csrf=$csrf"))) ?>
        </li>
        <?php else: ?>
        <li class="g-info">
          <?php echo  t("Maintenance mode is off.  User access is permitted.  <a href=\"%enable_maintenance_mode_url\">Turn on maintenance mode</a>", array("enable_maintenance_mode_url" => url::site("admin/maintenance/maintenance_mode/1?csrf=$csrf"))) ?>
        </li>
        <?php endif ?>
      </ul>
    </div>
  </div>

  <div class="g-block-content">
    <div id="g-available-tasks">
      <h2> <?php echo  t("Maintenance tasks") ?> </h2>
      <p>
        <?php echo  t("Occasionally your Gallery will require some maintenance.  Here are some tasks you can use to keep it running smoothly.") ?>
      </p>
      <table>
        <tr>
          <th>
            <?php echo  t("Name") ?>
          </th>
          <th>
            <?php echo  t("Description") ?>
          </th>
          <th>
            <?php echo  t("Action") ?>
          </th>
        </tr>
        <?php foreach ($task_definitions as $task): ?>
        <tr class="<?php echo  text::alternate("g-odd", "g-even") ?> <?php echo  log::severity_class($task->severity) ?>">
          <td class="<?php echo  log::severity_class($task->severity) ?>">
            <?php echo  $task->name ?>
          </td>
          <td>
            <?php echo  $task->description ?>
          </td>
          <td>
            <a href="<?php echo  url::site("admin/maintenance/start/$task->callback?csrf=$csrf") ?>"
              class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all">
              <?php echo  t("run") ?>
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </table>
    </div>

    <?php if ($running_tasks->count()): ?>
    <div id="g-running-tasks">
      <a href="<?php echo  url::site("admin/maintenance/cancel_running_tasks?csrf=$csrf") ?>"
         class="g-button g-right ui-icon-left ui-state-default ui-corner-all">
        <?php echo  t("cancel all running") ?></a>
      <h2> <?php echo  t("Running tasks") ?> </h2>
      <table>
        <tr>
          <th>
            <?php echo  t("Last updated") ?>
          </th>
          <th>
            <?php echo  t("Name") ?>
          </th>
          <th>
            <?php echo  t("Status") ?>
          </th>
          <th>
            <?php echo  t("Info") ?>
          </th>
          <th>
            <?php echo  t("Owner") ?>
          </th>
          <th>
            <?php echo  t("Action") ?>
          </th>
        </tr>
        <?php foreach ($running_tasks as $task): ?>
        <tr class="<?php echo  text::alternate("g-odd", "g-even") ?> <?php echo  $task->state == "stalled" ? "g-warning" : "" ?>">
          <td class="<?php echo  $task->state == "stalled" ? "g-warning" : "" ?>">
            <?php echo  gallery::date_time($task->updated) ?>
          </td>
          <td>
            <?php echo  $task->name ?>
          </td>
          <td>
            <?php if ($task->done): ?>
            <?php if ($task->state == "cancelled"): ?>
            <?php echo  t("Cancelled") ?>
            <?php endif ?>
            <?php echo  t("Close") ?>
            <?php elseif ($task->state == "stalled"): ?>
            <?php echo  t("Stalled") ?>
            <?php else: ?>
            <?php echo  t("%percent_complete% Complete", array("percent_complete" => $task->percent_complete)) ?>
            <?php endif ?>
          </td>
          <td>
            <?php echo  $task->status ?>
          </td>
          <td>
            <?php echo  html::clean($task->owner()->name) ?>
          </td>
          <td>
            <?php if ($task->state == "stalled"): ?>
            <a class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"
               href="<?php echo  url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>">
              <?php echo  t("resume") ?>
            </a>
            <?php endif ?>
            <?php if ($task->get_log()): ?>
            <a href="<?php echo  url::site("admin/maintenance/show_log/$task->id?csrf=$csrf") ?>" class="g-dialog-link g-button ui-state-default ui-corner-all">
              <?php echo  t("view log") ?>
            </a>
            <?php endif ?>
            <a href="<?php echo  url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>"
               class="g-button ui-icon-left ui-state-default ui-corner-all">
              <?php echo  t("cancel") ?>
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </table>
    </div>
    <?php endif ?>

    <?php if ($finished_tasks->count()): ?>
    <div id="g-finished-tasks">
      <a href="<?php echo  url::site("admin/maintenance/remove_finished_tasks?csrf=$csrf") ?>"
           class="g-button g-right ui-icon-left ui-state-default ui-corner-all">
         <span class="ui-icon ui-icon-trash"></span><?php echo  t("remove all finished") ?></a>
      <h2> <?php echo  t("Finished tasks") ?> </h2>
      <table>
        <tr>
          <th>
            <?php echo  t("Last updated") ?>
          </th>
          <th>
            <?php echo  t("Name") ?>
          </th>
          <th>
            <?php echo  t("Status") ?>
          </th>
          <th>
            <?php echo  t("Info") ?>
          </th>
          <th>
            <?php echo  t("Owner") ?>
          </th>
          <th>
            <?php echo  t("Action") ?>
          </th>
        </tr>
        <?php foreach ($finished_tasks as $task): ?>
        <tr class="<?php echo  text::alternate("g-odd", "g-even") ?> <?php echo  $task->state == "success" ? "g-success" : "g-error" ?>">
          <td class="<?php echo  $task->state == "success" ? "g-success" : "g-error" ?>">
            <?php echo  gallery::date_time($task->updated) ?>
          </td>
          <td>
            <?php echo  $task->name ?>
          </td>
          <td>
            <?php if ($task->state == "success"): ?>
            <?php echo  t("Success") ?>
            <?php elseif ($task->state == "error"): ?>
            <?php echo  t("Failed") ?>
            <?php elseif ($task->state == "cancelled"): ?>
            <?php echo  t("Cancelled") ?>
            <?php endif ?>
          </td>
          <td>
            <?php echo  $task->status ?>
          </td>
          <td>
            <?php echo  html::clean($task->owner()->name) ?>
          </td>
          <td>
            <?php if ($task->done): ?>
            <a href="<?php echo  url::site("admin/maintenance/remove/$task->id?csrf=$csrf") ?>" class="g-button ui-state-default ui-corner-all">
              <?php echo  t("remove") ?>
            </a>
            <?php if ($task->get_log()): ?>
            <a href="<?php echo  url::site("admin/maintenance/show_log/$task->id?csrf=$csrf") ?>" class="g-dialog-link g-button ui-state-default ui-corner-all">
              <?php echo  t("view log") ?>
            </a>
            <?php endif ?>
            <?php else: ?>
            <a href="<?php echo  url::site("admin/maintenance/resume/$task->id?csrf=$csrf") ?>" class="g-dialog-link g-button" ui-state-default ui-corner-all>
              <?php echo  t("resume") ?>
            </a>
            <a href="<?php echo  url::site("admin/maintenance/cancel/$task->id?csrf=$csrf") ?>" class="g-button ui-state-default ui-corner-all">
              <?php echo  t("cancel") ?>
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
