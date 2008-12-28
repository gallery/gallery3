<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gMaintenance">
  <h1> <?= _("Maintenance Tasks") ?> </h1>
  <p>
    <?= _("Occasionally your Gallery will require some maintenance.  Here are some tasks you can run to keep it running smoothly.") ?>
  </p>

  <div id="gAvailableTasks">
    <h2> <?= _("Available Tasks") ?> </h2>
    <table style="width: 400px">
      <? foreach ($available_tasks as $task) ?>
      <tr>
        <td>
          <?= $task->description ?>
        </td>
        <td>
          <a href="<?= url::site("admin/maintenance/start/$task->name") ?>" class="gDialogLink">
            <?= _("run") ?>
          </a>
        </td>
      </tr>
    </table>
  </div>

  <div id="gRunningTasks">
    <h2> <?= _("Running Tasks") ?> </h2>

    <i> Task list goes here </i>
  </div>
</div>
