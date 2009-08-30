<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  dismiss = function() {
    window.location.reload();
  }
  download = function(){
    // send request
    $('<form action="<?= url::site("admin/maintenance/save_log/$task->id?csrf=$csrf") ?>" method="post"></form>').
appendTo('body').submit().remove();
  };
</script>
<div id="gTaskLogDialog">
  <h1> <?= $task->name ?> </h1>
  <div class="gTaskLog">
    <pre><?= html::purify($task->get_log()) ?></pre>
  </div>
  <button id="gCloseButton" class="ui-state-default ui-corner-all" onclick="dismiss()"><?= t("Close") ?></button>
  <button id="gSaveButton" class="ui-state-default ui-corner-all" onclick="download()"><?= t("Save") ?></button>
</div>
