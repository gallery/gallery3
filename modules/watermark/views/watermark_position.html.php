<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gWatermarkAdmin">
  <div id="gTargetImage" class="droppable">
    <img src="<?= $sample_image ?>"></img>
  </div>
  <div id="gWaterMark">
    <!--  This style and div is only temporary -->
    <div style="background-color: #cccccc;">
    <img src="<?= $watermark_image ?>" class="draggable"
        width="<?= $watermark_width ?>"  height="<?= $watermark_height ?>" />
    </div>
  </div>
  <div id="gWatermarkPostionForm" >
  <?= $watermark_position_form ?>
  </div>
</div>