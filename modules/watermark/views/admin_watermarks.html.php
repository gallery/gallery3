<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block">
  <h1> <?php echo t("Watermarks") ?> </h1>
  <p>
    <?php echo t("You can have one watermark for your Gallery.  This watermark will be applied to all thumbnails and resized images, but it will not be applied to your full size images.  To make sure that your guests can only see watermarked images, you should restrict access to your full size images.") ?>
  </p>

  <div class="g-block-content">
    <?php if (empty($name)): ?>
    <a href="<?php echo url::site("admin/watermarks/form_add") ?>"
       title="<?php echo t("Upload a watermark")->for_html_attr() ?>"
       class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"><span class="ui-icon ui-icon-document-b"></span><?php echo t("Upload a watermark") ?></a>
    <?php else: ?>
    <h2> <?php echo t("Active watermark") ?> </h2>
    <p>
      <?php echo t("Note that changing this watermark will require you to rebuild all of your thumbnails and resized images.") ?>
    </p>
    <div>
      <div class="g-photo">
        <img width="<?php echo $width ?>" height="<?php echo $height ?>" src="<?php echo $url ?>" />
        <p>
          <?php echo t("Position: %position", array("position" => watermark::position($position))) ?>
        </p>
        <p>
          <?php echo t("Transparency: %transparency%", array("transparency" => module::get_var("watermark", "transparency"))) ?>
        </p>
      </div>
      <div class="controls">
        <a href="<?php echo url::site("admin/watermarks/form_edit") ?>"
           title="<?php echo t("Edit watermark")->for_html_attr() ?>"
           class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"><span class="ui-icon ui-icon-pencil"></span><?php echo t("edit") ?></a>
        <a href="<?php echo url::site("admin/watermarks/form_delete") ?>"
           title="<?php echo t("Delete watermark")->for_html_attr() ?>"
           class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span><?php echo t("delete") ?></a>
      </div>
    </div>
    <?php endif ?>
  </div>
</div>
