<script>
$("#gWatermarkAdmin").ready(function() {

  var container = $("#gDialog").parent().parent();
  var container_height = $(container).attr("offsetHeight");
  var container_width = $(container).attr("offsetWidth");

  var new_height = $("#gDialog").attr("offsetHeight") +
    container.find("div.ui-dialog-titlebar").attr("offsetHeight") +
    container.find("div.ui-dialog-buttonpane").attr("offsetHeight");
  container.css("height", Math.max(new_height, container_height) + "px");
  container.css("width", Math.max($("#gDialog").attr("offsetWidth"), container_width) + "px");
});
</script>
<div id="gWatermarkAdmin">
  <div id="gTarget" class="droppable">
    <img id="gTargetImage" src="<?= $sample_image ?>"></img>
    <div id="gWaterMark" style="float:none;z-index:1005;position:absolute;top:100px">
      <img id ="gWaterMarkImage" src="<?= $watermark_image ?>" class="draggable"
          width="<?= $watermark_width ?>"  height="<?= $watermark_height ?>" />
    </div>
  </div>
  <div id="gWatermarkPostionForm" >
  <?= $watermark_position_form ?>
  </div>
</div>