<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="#gWatermarks">
  <h1> <?= t("Watermarks") ?> </h1>
  <p>
    <?= t("You can have one watermark for your Gallery.  This watermark will be applied to all thumbnails and resized images, but it will not be applied to your full size images.  To make sure that your guests can only see watermarked images, you should restrict access to your full size images.") ?>
  </p>

  <? if (empty($name)): ?>
  <a href="<?= url::site("admin/watermarks/form_add") ?>"
     title="<?= t("Upload a watermark") ?>"
     class="gDialogLink"><?= t("Upload a watermark") ?></a>
  <? else: ?>
  <h2> <?= t("Active Watermark") ?> </h2>
  <p>
    <?= t("Note that changing this watermark will require you to rebuild all of your thumbnails and resized images.") ?>
  </p>
  <div>
    <div class="image">
      <img width="<?= $width ?>" height="<?= $height ?>" src="<?= $url ?>"/>
      <p>
        <?= t("Position: %position", array("position" => watermark::position($position))) ?>
      </p>
      <p>
        <?= t("Transparency: %transparency%", array("transparency" => module::get_var("watermark", "transparency"))) ?>
      </p>
    </div>
    <div class="controls">
      <a href="<?= url::site("admin/watermarks/form_edit") ?>"
         title="<?= t("Edit Watermark") ?>"
         class="gDialogLink"><?= t("edit") ?></a>
      <a href="<?= url::site("admin/watermarks/form_delete") ?>"
         title="<?= t("Delete Watermark") ?>"
         class="gDialogLink"><?= t("delete") ?></a>
    </div>
  </div>
  <? endif ?>
</div>
