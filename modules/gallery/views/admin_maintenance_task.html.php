<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var target_value;
  var animation = null;
  var delta = 1;
  var consecutive_error_count = 0;
  animate_progress_bar = function() {
    var current_value = parseInt($(".g-progress-bar div").css("width").replace("%", ""));
    if (target_value > current_value) {
      // speed up
      delta = Math.min(delta + 0.04, 3);
    } else {
      // slow down
      delta = Math.max(delta - 0.05, 1);
    }

    if (target_value == 100) {
      $(".g-progress-bar").progressbar("value", 100);
    } else if (current_value != target_value || delta != 1) {
      var new_value = Math.min(current_value + delta, target_value);
      $(".g-progress-bar").progressbar("value", new_value);
      animation = setTimeout(function() { animate_progress_bar(target_value); }, 100);
    } else {
      animation = null;
      delta = 1;
    }
    $.fn.gallery_hover_init();
  }

  var FAILED_MSG = <?= t("Something went wrong...sorry!  <a>Retry</a> or check the task log for details")->for_js() ?>;
  var ERROR_MSG = <?= t("Something went wrong!  Trying again in a moment... (__COUNT__)")->for_js() ?>;
  update = function() {
    $.ajax({
      url: <?= html::js_string(url::site("admin/maintenance/run/$task->id?csrf=$csrf")) ?>,
      dataType: "json",
      success: function(data) {
        target_value = data.task.percent_complete;
        consecutive_error_count = 0;
        if (!animation) {
          animate_progress_bar();
        }
        $("#g-status").html("" + data.task.status);
        if (data.task.done) {
          $("#g-pause-button").hide();
          $("#g-done-button").show();
        } else {
          setTimeout(update, 100);
        }
      },
      error: function(req, textStatus, errorThrown) {
        if (textStatus == "timeout" || textStatus == "parsererror") {
          consecutive_error_count++;
          if (consecutive_error_count == 5) {
            $("#g-status").html(FAILED_MSG);
            $("#g-pause-button").hide();
            $("#g-done-button").show();
            consecutive_error_count = 0;  // in case of a manual retry
            $("#g-status a").attr("href", "javascript:update()");
          } else {
            $("#g-status").html(ERROR_MSG.replace("__COUNT__", consecutive_error_count));
            // Give a little time to back off before retrying
            setTimeout(update, 1500 * consecutive_error_count);
          }
        }
      }
    });
  }
  $(".g-progress-bar").progressbar({value: 0});
  update();
  dismiss = function() {
    window.location.reload();
  }
</script>
<div id="g-progress">
  <h1> <?= $task->name ?> </h1>
  <div class="g-progress-bar"></div>
  <div id="g-status">
    <?= t("Starting up...") ?>
  </div>
  <div class="g-text-right">
    <button id="g-pause-button" class="ui-state-default ui-corner-all" onclick="dismiss()"><?= t("Pause") ?></button>
    <button id="g-done-button" class="ui-state-default ui-corner-all" style="display: none" onclick="dismiss()"><?= t("Close") ?></button>
  </div>
</div>
