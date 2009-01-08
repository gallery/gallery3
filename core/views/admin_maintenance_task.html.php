<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  update = function() {
    $.ajax({
      url: "<?= url::site("admin/maintenance/run/$task->id?csrf=$csrf") ?>",
      dataType: "json",
      success: function(data) {
        $("#gStatus").html("" + data.task.status);
        $("#gPercentComplete").html("" + data.task.percent_complete);
        if (data.task.done) {
          $("#gPauseButton").hide();
          $("#gDoneButton").show();
        } else {
          setTimeout(update, 100);
        }
      }
    });
  }
  update();
  dismiss = function() {
    window.location.reload();
  }
</script>
<div id="gProgressBar">
  status: <span id="gStatus"></span>
  <br/>
  percent_complete: <span id="gPercentComplete"></span>
  <div>
    <button id="gPauseButton" onclick="dismiss()"><?= t("Pause") ?></button>
    <button id="gDoneButton" style="display: none" onclick="dismiss()"><?= t("Done") ?></button>
  </div>
</div>
