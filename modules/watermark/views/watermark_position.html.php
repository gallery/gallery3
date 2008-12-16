<script>
$("#gDialog").ready(watermark_dialog_initialize());
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