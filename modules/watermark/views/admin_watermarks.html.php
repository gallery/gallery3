<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="#gWatermarks">
  <h1> <?= _("Watermarks") ?> </h1>
  <p>
    <?= _("You can have one watermark for your Gallery.  This watermark will be applied to all thumbnails and resized images, but it will not be applied to your full size images.  To make sure that your guests can only see watermarked images, you should restrict access to your full size images.") ?>
  </p>

  <? if (empty($name)): ?>
  <a href="<?= url::site("admin/watermarks/form_add") ?>"
     title="<?= _("Upload a watermark") ?>"
     class="gDialogLink"><?= _("Upload a watermark") ?></a>
  <? else: ?>
  <h2> <?= _("Active Watermark") ?> </h2>
  <p>
    <?= _("Note that changing this watermark will rebuild all of your thumbnails and resized images.") ?>
  </p>
  <p>
    <div class="image">
      <img width="<?= $width ?>" height="<?= $height ?>" src="<?= $url ?>"/>
      <p>
        <?= sprintf(_("Position: %s"), watermark::position($position)) ?>
      </p>
    </div>
    <div class="controls">
      <a href="<?= url::site("admin/watermarks/form_edit") ?>"
         title="<?= _("Edit Watermark") ?>"
         class="gDialogLink"><?= _("edit") ?></a>
      <a href="<?= url::site("admin/watermarks/form_delete") ?>"
         title="<?= _("Delete Watermark") ?>"
         class="gDialogLink"><?= _("delete") ?></a>
    </div>
  </p>
  <? endif ?>
</div>
