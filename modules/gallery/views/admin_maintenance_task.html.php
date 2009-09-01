<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var target_value;
  var animation = null;
  var delta = 1;
  animate_progress_bar = function() {
    var current_value = parseInt($(".gProgressBar div").css("width").replace("%", ""));
    if (target_value > current_value) {
      // speed up
      delta = Math.min(delta + 0.04, 3);
    } else {
      // slow down
      delta = Math.max(delta - 0.05, 1);
    }

    if (target_value == 100) {
      $(".gProgressBar").progressbar("value", 100);
    } else if (current_value != target_value || delta != 1) {
      var new_value = Math.min(current_value + delta, target_value);
      $(".gProgressBar").progressbar("value", new_value);
      animation = setTimeout(function() { animate_progress_bar(target_value); }, 100);
    } else {
      animation = null;
      delta = 1;
    }
    $.fn.gallery_hover_init();
  }

  update = function() {
    $.ajax({
      url: <?= html::js_string(url::site("admin/maintenance/run/$task->id?csrf=$csrf")) ?>,
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
    window.location.reload();
  }
</script>
<div id="gProgress">
  <h1> <?= $task->name ?> </h1>
  <div class="gProgressBar"></div>
  <div id="gStatus">
    <?= t("Starting up...") ?>
  </div>
  <div class="txtright">
    <button id="gPauseButton" class="ui-state-default ui-corner-all" onclick="dismiss()"><?= t("Pause") ?></button>
    <button id="gDoneButton" class="ui-state-default ui-corner-all" style="display: none" onclick="dismiss()"><?= t("Close") ?></button>
  </div>
</div>
