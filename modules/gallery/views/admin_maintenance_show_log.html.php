<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  dismiss = function() {
    window.location.reload();
  };
  download = function() {
    // send request
    $('<form action="<?= url::site("admin/maintenance/save_log/$task->id?csrf=$csrf") ?>" method="post"></form>').
appendTo('body').submit().remove();
  };
</script>
<div id="g-task-log-dialog">
  <h1> <?= $task->name ?> </h1>
  <div class="g-task-log g-text-small">
    <pre><?= html::purify($task->get_log()) ?></pre>
  </div>
  <button id="g-close" class="ui-state-default ui-corner-all" onclick="dismiss()"><?= t("Close") ?></button>
  <button id="g-save" class="ui-state-default ui-corner-all" onclick="download()"><?= t("Download") ?></button>
</div>
