<? defined("SYSPATH") or die("No direct script access."); ?>
<script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
<script type="text/javascript">
  update = function() {
    $.ajax({
      url: "<?= url::site("admin/maintenance/run/$task->id?csrf=$csrf") ?>",
      dataType: "json",
      success: function(data) {
        $("#gStatus").html("" + data.task.status);
        $("#gPercentComplete").html("" + data.task.percent_complete);
        if (!data.task.done) {
          setTimeout(update, 100);
        }
      }
    });
  }
  update();
</script>
<div id="gProgressBar">
  status: <span id="gStatus"></span>
  <br/>
  percent_complete: <span id="gPercentComplete"></span>
</div>
