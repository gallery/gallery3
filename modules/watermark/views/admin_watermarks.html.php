<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block">
  <h1> <?= t("Watermarks") ?> </h1>
  <p>
    <?= t("You can have one watermark for your Gallery.  This watermark will be applied to all thumbnails and resized images, but it will not be applied to your full size images.  To make sure that your guests can only see watermarked images, you should restrict access to your full size images.") ?>
  </p>

  <div class="g-block-content">
    <? if (empty($name)): ?>
    <a href="<?= url::site("admin/watermarks/form_add") ?>"
       title="<?= t("Upload a watermark")->for_html_attr() ?>"
       class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"><span class="ui-icon ui-icon-document-b"></span><?= t("Upload a watermark") ?></a>
    <? else: ?>
    <h2> <?= t("Active watermark") ?> </h2>
    <p>
      <?= t("Note that changing this watermark will require you to rebuild all of your thumbnails and resized images.") ?>
    </p>
    <div>
      <div class="g-photo">
        <img width="<?= $width ?>" height="<?= $height ?>" src="<?= $url ?>" />
        <p>
          <?= t("Position: %position", array("position" => watermark::position($position))) ?>
        </p>
        <p>
          <?= t("Transparency: %transparency%", array("transparency" => module::get_var("watermark", "transparency"))) ?>
        </p>
      </div>
      <div class="controls">
        <a href="<?= url::site("admin/watermarks/form_edit") ?>"
           title="<?= t("Edit watermark")->for_html_attr() ?>"
           class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"><span class="ui-icon ui-icon-pencil"></span><?= t("edit") ?></a>
        <a href="<?= url::site("admin/watermarks/form_delete") ?>"
           title="<?= t("Delete watermark")->for_html_attr() ?>"
           class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all"><span class="ui-icon ui-icon-trash"></span><?= t("delete") ?></a>
      </div>
    </div>
    <? endif ?>
  </div>
</div>
