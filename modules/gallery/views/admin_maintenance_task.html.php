<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var target_value;
  var animation = null;
  var delta = 1;
  animate_progress_bar = function() {
    var current_value = Number($(".gProgressBar div").css("width").replace("%", ""));
    if (current_value != target_value) {
      var new_value = Math.min(current_value + delta, target_value);
      if (target_value - current_value > delta) {
        delta += .075;
      }
      if (target_value == 100) {
        new_value = 100;
      }
      $(".gProgressBar").progressbar("value", new_value);
      animation = setTimeout(function() { animate_progress_bar(target_value); }, 100);
    } else {
      animation = null;
      delta = 1;
    }
  }

  update = function() {
    $.ajax({
      url: "<?= url::site("admin/maintenance/run/$task->id?csrf=$csrf") ?>",
      dataType: "json",
      success: function(data) {
        target_value = data.task.percent_complete;
        if (!animation) {
          animate_progress_bar();
        }
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
  $(".gProgressBar").progressbar({value: 0});
  update();
  dismiss = function() {
    $.gallery_reload();
  }
</script>
<div id="gProgress">
  <h1> <?= $task->name ?> </h1>
  <div class="gProgressBar"></div>
  <div id="gStatus">
    <?= t("Starting up...") ?>
  </div>
  <div>
    <button id="gPauseButton" class="ui-state-default ui-corner-all" onclick="dismiss()"><?= t("Pause") ?></button>
    <button id="gDoneButton" class="ui-state-default ui-corner-all" style="display: none" onclick="dismiss()"><?= t("Close") ?></button>
  </div>
</div>
