<script>
$("#gDialog").ready(function() {
  var container = $("#gDialog").parent().parent();
  var container_height = $(container).attr("offsetHeight");
  var container_width = $(container).attr("offsetWidth");

  var new_height = $("#gDialog").attr("offsetHeight") +
    container.find("div.ui-dialog-titlebar").attr("offsetHeight") +
    container.find("div.ui-dialog-buttonpane").attr("offsetHeight");
  var height = Math.max(new_height, container_height);
  var width = Math.max($("#gDialog").attr("offsetWidth"), container_width);
  container.css("height", height + "px");
  container.css("width", width + "px");
  container.css("top", ((document.height - height) / 2) + "px");
  container.css("left", ((document.width - width) / 2) + "px");
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