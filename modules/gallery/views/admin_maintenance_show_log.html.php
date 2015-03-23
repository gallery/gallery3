<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  dismiss = function() {
    window.location.reload();
  };
  download = function() {
    // send request
    $('<form action="<?php echo url::site("admin/maintenance/save_log/$task->id?csrf=$csrf") ?>" method="post"></form>').
appendTo('body').submit().remove();
  };
</script>
<div id="g-task-log-dialog">
  <h1> <?php echo $task->name ?> </h1>
  <div class="g-task-log g-text-small">
    <pre><?php echo html::purify($task->get_log()) ?></pre>
  </div>
  <button id="g-close" class="ui-state-default ui-corner-all" onclick="dismiss()"><?php echo t("Close") ?></button>
  <button id="g-save" class="ui-state-default ui-corner-all" onclick="download()"><?php echo t("Download") ?></button>
</div>
