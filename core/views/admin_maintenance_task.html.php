<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  update = function() {
    $.ajax({
      url: "<?= url::site("admin/maintenance/run/$task->id?csrf=$csrf") ?>",
      dataType: "json",
      success: function(data) {
        $("#gProgressBar").progressbar("value", data.task.percent_complete);
        $("#gStatus").html("" + data.task.status);
        if (data.task.done) {
          $("#gPauseButton").hide();
          $("#gDoneButton").show();
        } else {
          setTimeout(update, 100);
        }
      }
    });
  }
  $("#gProgressBar").progressbar({value: 0});
  update();
  dismiss = function() {
    window.location.reload();
  }
</script>
<div id="gProgress">
  <div id="gProgressBar"></div>
  <div id="gStatus"></div>
  <div>
    <button id="gPauseButton" onclick="dismiss()"><?= t("Pause") ?></button>
    <button id="gDoneButton" style="display: none" onclick="dismiss()"><?= t("Done") ?></button>
  </div>
</div>
